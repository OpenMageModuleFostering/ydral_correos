<?php 

/**
 *
 */
class Ydral_Correos_Model_Entregahoraria extends Mage_Core_Model_Abstract
{
    
    protected function _construct()
    {
        $this->_init('correos/entregahoraria');
    }	
    
    
    /**
     *
     */
    public function checkCp($cp)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('cp', $cp)
            ->getFirstItem();
        if($collection->getId()) { 
            return true;
        }else{
            return false;
        }
    }
    
}