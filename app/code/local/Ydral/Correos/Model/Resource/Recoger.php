<?php 

/**
 *
 */
class Ydral_Correos_Model_Resource_Recoger extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('correos/recoger', 'id');
    }	

}