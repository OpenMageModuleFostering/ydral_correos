<?php 

/**
 *
 */
class Ydral_Correos_Model_Resource_Registro_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('correos/registro');
    }	
}