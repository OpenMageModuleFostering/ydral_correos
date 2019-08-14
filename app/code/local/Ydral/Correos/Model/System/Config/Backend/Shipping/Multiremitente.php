<?php

/**
 *
 */
class Ydral_Correos_Model_System_Config_Backend_Shipping_Multiremitente extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
		Mage::getResourceModel('correos/remitente')->uploadAndImport($this);
    }
}
