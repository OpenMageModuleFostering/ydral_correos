<?php 

/**
 *
 */
class Ydral_Correos_Model_Resource_Remitente extends Mage_Core_Model_Mysql4_Abstract
{
    
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('correos/remitente', 'id');
    }	   


    /**
    *
    */
    public function uploadAndImport(Varien_Object $object)
    {
        
        $csvFile = null;
        if (isset($_FILES["groups"]["tmp_name"]["remitente"]["fields"]["importsender"]["value"])) {
            $csvFile = $_FILES["groups"]["tmp_name"]["remitente"]["fields"]["importsender"]["value"];
        }
        

        if (!empty($csvFile)) 
        {
            $csv = trim(file_get_contents($csvFile));
            $table = Mage::getSingleton('core/resource')->getTableName('correos/remitente');
      
            if (!empty($csv)) 
            {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);
                
                if (count($csvLine) < 13) 
                {
                    $exceptions[0] = Mage::helper('correos')->__('Formato de fichero de remitentes inválido.');
                }

                $countryCodes = array();
                $regionCodes = array();
                
                foreach ($csvLines as $k => $csvLine) 
                {
                    $csvLine = $this->_getCsvValues($csvLine);
                    
                    if (count($csvLine) > 0 && count($csvLine) < 13) 
                    {
                        $exceptions[0] = Mage::helper('correos')->__('Formato de fichero de remitentes inválido.');
                    } 
                    else 
                    {
                        $countryCodes[] = $csvLine[5];
                        $regionCodes[] = $csvLine[6];
                    }
                }

                if (empty($exceptions)) 
                {
                    $data = array();
                    $countryCodesToIds = array();
                    $regionCodesToIds = array();
                    $countryCodesIso2 = array();

                    $countryCollection = Mage::getResourceModel('directory/country_collection')->addCountryCodeFilter($countryCodes)->load();
                    
                    foreach ($countryCollection->getItems() as $country) 
                    {
                        $countryCodesToIds[$country->getData('iso3_code')] = $country->getData('country_id');
                        $countryCodesToIds[$country->getData('iso2_code')] = $country->getData('country_id');
                        $countryCodesIso2[] = $country->getData('iso2_code');
                    }

                    $regionCollection = Mage::getResourceModel('directory/region_collection')
                        ->addRegionCodeFilter($regionCodes)
                        ->addCountryFilter($countryCodesIso2)
                        ->load();

                    foreach ($regionCollection->getItems() as $region) 
                    {
                        $regionCodesToIds[$countryCodesToIds[$region->getData('country_id')]][$region->getData('code')] = $region->getData('region_id');                        
                    }
                    
                    foreach ($csvLines as $k => $csvLine) 
                    {
                    	
                        $csvLine = $this->_getCsvValues($csvLine);

                        /*
                         * Column 1 - Nombre
                         */
                        if ($csvLine[0] == '') { $nombre = ''; } 
                        else { $nombre = $csvLine[0]; }
                        
                        /*
                         * Column 2 - Apellidos
                         */
                        if ($csvLine[1] == '') { $apellidos = ''; } 
                        else { $apellidos = $csvLine[1]; }
                        
                        /*
                         * Column 3 - NIF
                         */
                        if ($csvLine[2] == '') { $nif = ''; } 
                        else { $nif = $csvLine[2]; }
                        
                        /*
                         * Column 4 - Empresa
                         */
                        if ($csvLine[3] == '') { $empresa = ''; } 
                        else { $empresa = $csvLine[3]; }
                        
                        /*
                         * Column 4 - Persona_de_contacto
                         */
                        if ($csvLine[4] == '') { $personacontacto = ''; } 
                        else { $personacontacto = $csvLine[4]; }
                        
                        if ($empresa == '' && $nombre == '') 
                        {
                            $exceptions[] = Mage::helper('correos')->__('Debe indicarse un nombre o una empresa en la dirección de la línea #%s', ($k+1));
                        }
                         
                        /**
                         *  Column 6 - Country
                         */
                        if (empty($countryCodesToIds) || !array_key_exists($csvLine[5], $countryCodesToIds)) 
                        {
                            $countryId = '0';
                            if ($csvLine[5] != '') { $exceptions[] = Mage::helper('correos')->__('Invalid Country "%s" in the Row #%s', $csvLine[5], ($k+1)); }
                        } else {
                            $countryId = $countryCodesToIds[$csvLine[5]];
                        }
                        
                        /*
                         * Column 7 - Region/State
                         */
	 					if ($countryId == '0')
	 					{
	 						$regionId = '0';
	 					} else {
                        	if (empty($regionCodesToIds[$countryCodesToIds[$csvLine[5]]]) || !array_key_exists($csvLine[6], $regionCodesToIds[$countryCodesToIds[$csvLine[5]]])) 
                        	{
                            	$regionId = '0';
                            	if ($csvLine[6] != '') { $exceptions[] = Mage::helper('correos')->__('Invalid Region/State "%s" in the Row #%s', $csvLine[6], ($k+1)); }
                        	} else {
                            	$regionId = $regionCodesToIds[$countryCodesToIds[$csvLine[5]]][$csvLine[6]];
                        	}
	 					}
	 					
                        /*
                         * Column 8 - Zip/Postal Code
                         */
                        if ($csvLine[7] == '') 
                        { 
                            $zip = ''; 
                            $exceptions[] = Mage::helper('correos')->__('El código postal es un campo obligatorio en la línea #%s', ($k+1));
                        } else { $zip = $csvLine[7]; }

                        /*
                         * Column 9 - Localidad
                         */
                        if ($csvLine[8] == '') 
                        { 
                            $localidad = ''; 
                            $exceptions[] = Mage::helper('correos')->__('La localidad es un campo obligatorio en la línea #%s', ($k+1));
                        } else { $localidad = $csvLine[8]; }
                         
                        /*
                         * Column 10 - Dirección
                         */
                        if ($csvLine[9] == '') 
                        { 
                            $direccion = ''; 
                            $exceptions[] = Mage::helper('correos')->__('La dirección es un campo obligatorio en la línea #%s', ($k+1));
                        } else { $direccion = $csvLine[9]; }
                         
                        /*
                         * Column 11 - Teléfono
                         */
                        if ($csvLine[10] == '') { $telefono = ''; } 
                        else { $telefono = $csvLine[10]; }
                         
                        /*
                         * Column 12 - Email
                         */
                        if ($csvLine[11] == '') { $email = ''; } 
                        else { $email = $csvLine[11]; }
                         
                        /*
                         * Column 13 - Teléfono Móvil
                         */
                        if ($csvLine[12] == '') 
                        { 
                            $telefonosms = ''; 
                            $exceptions[] = Mage::helper('correos')->__('El teléfono móvil es un campo obligatorio en la línea #%s', ($k+1));
                        } elseif (!Mage::helper('correos')->validarMovil($csvLine[12])) {
                            $telefonosms = ''; 
                            $exceptions[] = Mage::helper('correos')->__('El teléfono móvil es incorrecto en la línea #%s', ($k+1));
                        } else { $telefonosms = $csvLine[12]; }
                        
                        
                        
                        $data[] = array(
                            'nombre' => $nombre,
                            'apellidos' => $apellidos,
                            'dni' => $nif,
                            'empresa' => $empresa,
                            'persona_contacto' => $personacontacto,
                        	'pais' => $countryId, 
                        	'provincia' => $regionId, 
                        	'cp' => $zip, 
                        	'localidad' => $localidad,
                        	'direccion' => $direccion,
                        	'telefono' => $telefono, 
                        	'email' => $email,
                        	'telefono_movil' => $telefonosms,
                        );
                    }
                }
                

                
                if (empty($exceptions)) 
                {
                    $connection = $this->_getWriteAdapter();

                    $condition = array(
                        $connection->quoteInto('nombre = ?', $nombre),
                        $connection->quoteInto('pais = ?', $pais),
                        $connection->quoteInto('direccion = ?', $direccion),
                    );

                    //$connection->delete($table, $condition);
                    $connection->delete($table, '');

                    foreach($data as $k => $dataLine) 
                    {
                        try 
                        {
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