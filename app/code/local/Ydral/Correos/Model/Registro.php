<?php 

/**
 *
 */
class Ydral_Correos_Model_Registro extends Mage_Core_Model_Abstract
{
    
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('correos/registro');
    }	
    
    
    /**
     *
     */
    public function loadByOrder($orderId)
    {
        $collection = $this->getCollection()
                ->addFieldToFilter('order_id', $orderId);
        return $collection;
    }
    
    
    /**
     *
     */
    public function loadByCorreosId($correosId)
    {
        $collection = $this->getCollection()
                ->addFieldToFilter('num_registro', $correosId);
        return $collection;
    }
    
}