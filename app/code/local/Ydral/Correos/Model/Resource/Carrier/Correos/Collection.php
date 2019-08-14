<?php

/**
 *
 */
class Ydral_Correos_Model_Resource_Carrier_Correos_Collection extends Varien_Data_Collection_Db
{
    protected $_shipTable;
    protected $_countryTable;
    protected $_regionTable;

    public function __construct()
    {
        
        parent::__construct(Mage::getSingleton('core/resource')->getConnection('shipping_read'));
        
        $this->_shipTable       = Mage::getSingleton('core/resource')->getTableName('correos/correos');       
        $this->_countryTable    = Mage::getSingleton('core/resource')->getTableName('directory/country');        
        $this->_regionTable     = Mage::getSingleton('core/resource')->getTableName('directory/country_region');
        
        $this->_initSelect();        
        $this->_setIdFieldName('pk');
        
        return $this;
                
    }
    
    
    public function _initSelect()
    {

        $this->_select->from(array("main_table" => $this->_shipTable))
            ->joinLeft(
                array('country_table' => $this->_countryTable),
                'country_table.country_id = main_table.dest_country_id',
                array('dest_country' => 'iso3_code'))
            ->joinLeft(
                array('region_table' => $this->_regionTable),
                'region_table.region_id = main_table.dest_region_id',
                array('dest_region' => 'code'));

        $this->addOrder('dest_country', self::SORT_ORDER_ASC);
        $this->addOrder('dest_region', self::SORT_ORDER_ASC);
        $this->addOrder('dest_zip', self::SORT_ORDER_ASC);
        $this->addOrder('condition_value', self::SORT_ORDER_ASC);
    }
    

    public function setWebsiteFilter($websiteId)
    {
        return $this->addFieldToFilter('website_id', $websiteId);
    }

    public function setConditionFilter($conditionName)
    {
        return $this->addFieldToFilter('condition_name', $conditionName);
    }

    public function setCountryFilter($countryId)
    {
        return $this->addFieldToFilter('dest_country_id', $countryId);
    }
    
    public function setMethodFilter($methodName)
    {
        return $this->addFieldToFilter('method_code', $methodName);
    }
}