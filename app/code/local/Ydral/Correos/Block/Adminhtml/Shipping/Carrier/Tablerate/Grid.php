<?php

/**
 *
 */
class Ydral_Correos_Block_Adminhtml_Shipping_Carrier_Tablerate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    
    protected $_websiteId;
    protected $_conditionName;
    protected $_methodName;


    /**
     * Define grid properties
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('shippingTablerateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * 
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = Mage::app()->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * 
     */
    public function getWebsiteId()
    {
        if (is_null($this->_websiteId)) {
            $this->_websiteId = Mage::app()->getWebsite()->getId();
        }
        return $this->_websiteId;
    }

    /**
     * 
     */
    public function setConditionName($name)
    {
        $this->_conditionName = $name;
        return $this;
    }

    /**
     * 
     */
    public function getConditionName()
    {
        return $this->_conditionName;
    }
    
    /**
     * 
     */
    public function setMethodName($name)
    {
        $this->_methodName = $name;
        return $this;
    }

    /**
     * 
     */
    public function getMethodName()
    {
        return $this->_methodName;
    }


    /**
     * Prepare shipping table rate collection
     *
     * @return Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Ydral_Correos_Model_Resource_Carrier_Correos_Collection */
        $collection = Mage::getResourceModel('correos/carrier_correos_collection');
        $collection->setConditionFilter($this->getConditionName())
            ->setWebsiteFilter($this->getWebsiteId())
            ->setMethodFilter($this->getMethodName());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('dest_country', array(
            'header'    => Mage::helper('adminhtml')->__('Country'),
            'index'     => 'dest_country',
            'default'   => '*',
        ));

        $this->addColumn('dest_region', array(
            'header'    => Mage::helper('adminhtml')->__('Region/State'),
            'index'     => 'dest_region',
            'default'   => '*',
        ));

        $this->addColumn('dest_zip', array(
            'header'    => Mage::helper('adminhtml')->__('Zip/Postal Code'),
            'index'     => 'dest_zip',
            'default'   => '*',
        ));

        $label = Mage::getSingleton('shipping/carrier_tablerate')
            ->getCode('condition_name_short', $this->getConditionName());
        $this->addColumn('condition_value', array(
            'header'    => $label,
            'index'     => 'condition_value',
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('adminhtml')->__('Shipping Price'),
            'index'     => 'price',
        ));

        return parent::_prepareColumns();
    }
    
}