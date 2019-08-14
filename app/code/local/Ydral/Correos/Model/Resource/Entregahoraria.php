<?php 

/**
 *
 */
class Ydral_Correos_Model_Resource_Entregahoraria extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('correos/entregahoraria', 'id');
    }


    /**
    *
    */
    public function uploadAndImport(Varien_Object $object)
    {
        
        $csvFile = $_FILES["groups"]["tmp_name"]["entregahoraria"]["fields"]["importcps"]["value"];
        

        if (!empty($csvFile)) 
        {
            $csv = trim(file_get_contents($csvFile));
            $table = Mage::getSingleton('core/resource')->getTableName('correos/entregahoraria');
      
            if (!empty($csv)) 
            {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);
                
                if (count($csvLine) < 1) 
                {
                    $exceptions[0] = Mage::helper('correos')->__('Formato de fichero de CPs inválido.');
                }

                if (empty($exceptions)) 
                {
                    $data = array();
                                        
                    foreach ($csvLines as $k => $csvLine) 
                    {
                    	
                        $csvLine = $this->_getCsvValues($csvLine);

                        /*
                         * Column 1 - CP
                         */
                        if ($csvLine[0] == '') { continue; } 
                        else { $cp = $csvLine[0]; }
                                                
                        $data[] = str_pad($cp, 5, "0", STR_PAD_LEFT);
                    }
                }


                
                if (empty($exceptions)) 
                {
                    
                    $data = array_unique($data);
                    
                    $connection = $this->_getWriteAdapter();
                    $connection->delete($table, '');

                    foreach($data as $k => $valueCp) 
                    {
                        try 
                        {
                            $dataLine = array('cp' => $valueCp);
                            $connection->insert($table, $dataLine);
                        } 
                        catch (Exception $e) 
                        {
                        	// This should probably show the exception message too.
                            $exceptions[] = Mage::helper('correos')->__('Import error:' . $e->getMessage());
                        }
                    }
                }

                if (!empty($exceptions)) 
                {
                    throw new Exception( "\n" . implode("\n", $exceptions) );
                }
            }
        }
    }


    /**
     *
     */
    protected function _getCsvValues($string, $separator=",")
    {
        $elements = explode($separator, trim($string));
        
        for ($i = 0; $i < count($elements); $i++) 
        {
            $nquotes = substr_count($elements[$i], '"');
            
            if ($nquotes %2 == 1) 
            {
                for ($j = $i+1; $j < count($elements); $j++) 
                {
                    if (substr_count($elements[$j], '"') > 0) 
                    {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j-$i+1, implode($separator, array_slice($elements, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            
            if ($nquotes > 0) 
            {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
    }
}