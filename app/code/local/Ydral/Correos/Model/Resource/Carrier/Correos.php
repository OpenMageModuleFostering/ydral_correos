<?php

/**
 *
 */
class Ydral_Correos_Model_Resource_Carrier_Correos extends Mage_Core_Model_Resource_Db_Abstract
{
    
    /**
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('correos/correos', 'pk');
	}
	
	
    /**
     * Devuelve la tabla de tramos de envio o false
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return array|boolean
     */
	public function getRate(Mage_Shipping_Model_Rate_Request $request)
	{
	    
        $adapter = $this->_getReadAdapter();
        $bind = array(
            ':website_id' => (int) $request->getWebsiteId(),
            ':country_id' => $request->getDestCountryId(),
            ':region_id'  => (int) $request->getDestRegionId(),
            ':postcode'   => $request->getDestPostcode()
        );
        $select = $adapter->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('correos/correos'))
            ->where('website_id = :website_id')
            ->order(array('dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC'))
            ->limit(1);

        // Render destination condition
        $orWhere = '(' . implode(') OR (', array(
            "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode",
            "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = ''",

            // Handle asterix in dest_zip field
            "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = '*'",
            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = '*'",
            "dest_country_id = '0' AND dest_region_id = :region_id AND dest_zip = '*'",
            "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = '*'",

            "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = ''",

            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = ''",
            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode",
            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = '*'",
        )) . ')';
        $select->where($orWhere);
        
        
        // Render condition by condition name
        if (is_array($request->getConditionName())) {
            $orWhere = array();
            $i = 0;
            foreach ($request->getConditionName() as $conditionName) {
                $bindNameKey  = sprintf(':condition_name_%d', $i);
                $bindValueKey = sprintf(':condition_value_%d', $i);
                $orWhere[] = "(condition_name = {$bindNameKey} AND condition_value <= {$bindValueKey})";
                $bind[$bindNameKey] = $conditionName;
                $bind[$bindValueKey] = $request->getData($conditionName);
                $i++;
            }

            if ($orWhere) {
                $select->where(implode(' OR ', $orWhere));
            }
        } else {
            $bind[':condition_name']  = $request->getConditionName();
            $bind[':condition_value'] = $request->getData($request->getConditionName());

            $select->where('condition_name = :condition_name');
            $select->where('condition_value <= :condition_value');
            $select->where('method_code=?', $request->getMethodCode());
        }

        $result = $adapter->fetchRow($select, $bind);
        
        
        //  log
        Mage::helper('correos')->_logger('Consulta de petición de rates [' . $request->getMethodCode() . ']');
        $adapter->getProfiler()->setEnabled(true); //enable profiler
        Mage::helper('correos')->_logger($adapter->getProfiler()->getLastQueryProfile());
        
        
        // Normalize destination zip code
        if ($result && $result['dest_zip'] == '*') {
            $result['dest_zip'] = '';
        }
        return $result;
        
	}


    /**
    *
    */
    public function uploadAndImport(Varien_Object $object, $methodCode)
    {
        
        $csvFile = $_FILES["groups"]["tmp_name"][$methodCode]["fields"]["import"]["value"];
        

        if (!empty($csvFile)) 
        {
            $csv = trim(file_get_contents($csvFile));
            $table = Mage::getSingleton('core/resource')->getTableName('correos/correos');
            $websiteId = $object->getScopeId();

            if ($object->getData('groups/'.$methodCode.'/fields/condition_name/inherit') == '1') 
            {
                $conditionName = (string)Mage::getConfig()->getNode('default/carriers/'.$methodCode.'/condition_name');
            } else {
                $conditionName = $object->getData('groups/'.$methodCode.'/fields/condition_name/value');
            }

            $conditionFullName = Mage::getModel('correos/correos_correos')->getCode('condition_name_short', $conditionName);
      
            if (!empty($csv)) 
            {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);
                
                if (count($csvLine) < 5) 
                {
                    $exceptions[0] = Mage::helper('shipping')->__('Invalid Table Rates File Format');
                }

                $countryCodes = array();
                $regionCodes = array();
                
                foreach ($csvLines as $k => $csvLine) 
                {
                    $csvLine = $this->_getCsvValues($csvLine);
                    
                    if (count($csvLine) > 0 && count($csvLine) < 5) 
                    {
                        $exceptions[0] = Mage::helper('shipping')->__('Invalid Table Rates File Format');
                    } 
                    else 
                    {
                        $countryCodes[] = $csvLine[0];
                        $regionCodes[] = $csvLine[1];
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
                         * Column 1 - Country
                         */
                        if (empty($countryCodesToIds) || !array_key_exists($csvLine[0], $countryCodesToIds)) 
                        {
                            $countryId = '0';
                            
                            if ($csvLine[0] != '*' && $csvLine[0] != '') 
                            {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid Country "%s" in the Row #%s', $csvLine[0], ($k+1));
                            }
                        } 
                        else 
                        {
                            $countryId = $countryCodesToIds[$csvLine[0]];
                        }

                        
                        /*
                         * Column 2 - Region/State
                         */
	 					if ($countryId == '0')
	 					{
	 						$regionId = '0';
	 					}
	 					else
	 					{
                        	if (empty($regionCodesToIds[$countryCodesToIds[$csvLine[0]]]) || !array_key_exists($csvLine[1], $regionCodesToIds[$countryCodesToIds[$csvLine[0]]])) 
                        	{
                            	$regionId = '0';
                            
                            	if ($csvLine[1] != '*' && $csvLine[1] != '') 
                            	{
                                	$exceptions[] = Mage::helper('shipping')->__('Invalid Region/State "%s" in the Row #%s', $csvLine[1], ($k+1));
                            	}
                        	} 
                        	else 
                        	{
                            	$regionId = $regionCodesToIds[$countryCodesToIds[$csvLine[0]]][$csvLine[1]];
                        	}
	 					}
                        /*
                         * Column 3 - Zip/Postal Code
                         */
                        if ($csvLine[2] == '*' || $csvLine[2] == '') 
                        {
                            $zip = '';
                        } 
                        else 
                        {
                            $zip = $csvLine[2];
                        }

                        /*
                         * Column 4 - Order Subtotal
                         */
                        if (!$this->_isPositiveDecimalNumber($csvLine[3]) || $csvLine[3] == '*' || $csvLine[3] == '') 
                        {
                            $exceptions[] = Mage::helper('shipping')->__('Invalid %s "%s" in the Row #%s', $conditionFullName, $csvLine[3], ($k+1));
                        } 
                        else 
                        {
                            $csvLine[3] = (float)$csvLine[3];
                        }

                        /*
                         * Column 5 - Shipping Price
                         */
                        if (!$this->_isPositiveDecimalNumber($csvLine[4])) 
                        {
                            $exceptions[] = Mage::helper('shipping')->__('Invalid Shipping Price "%s" in the Row #%s', $csvLine[4], ($k+1));
                        } 
                        else 
                        {
                            $csvLine[4] = (float)$csvLine[4];
                        }
                        
                        
                        $data[] = array(
                        	'website_id' => $websiteId, 
                        	'dest_country_id' => $countryId, 
                        	'dest_region_id' => $regionId, 
                        	'dest_zip' => $zip, 
                        	'condition_name' => $conditionName,
                        	'condition_value' => $csvLine[3],
                        	'price' => $csvLine[4], 
                        	'method_code' => $methodCode,
                        );
                    }
                }
                

                
                if (empty($exceptions)) 
                {
                    $connection = $this->_getWriteAdapter();

                    $condition = array(
                        $connection->quoteInto('website_id = ?', $websiteId),
                        $connection->quoteInto('condition_name = ?', $conditionName),   
                        $connection->quoteInto('method_code = ?', $methodCode),         
                    );

                    $connection->delete($table, $condition);

                    foreach($data as $k=>$dataLine) 
                    {
                        try 
                        {
                            $connection->insert($table, $dataLine);
                        } 
                        catch (Exception $e) 
                        {
                        	// This should probably show the exception message too.
                            $exceptions[] = Mage::helper('shipping')->__('Import error: ' . $e->getMessage());
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


    /**
     *
     */
    protected function _isPositiveDecimalNumber($n)
    {
        return preg_match ("/^[0-9]+(\.[0-9]*)?$/", $n);
    }

}
