<?php

/**
 *
 */
class Ydral_Correos_Model_System_Config_Backend_Shipping_Abstract extends Mage_Core_Model_Config_Data
{
    protected $_method;
    
    public function _afterSave()
    {
		Mage::getResourceModel('correos/carrier_correos')->uploadAndImport($this, $this->_method);
    }
}
