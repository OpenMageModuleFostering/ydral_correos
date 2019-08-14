<?php 

/**
 *
 */
class Ydral_Correos_Model_Recoger extends Mage_Core_Model_Abstract
{
    
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('correos/recoger');
    }	
    
    
    /**
     *
     */
    public function getCheckoutData($entityType, $checkoutId)
    {
        $_dataTransaction = $this->getCollection()
            ->addFieldToFilter('entity_type', $entityType)
            ->addFieldToFilter('checkout_id', $checkoutId);
        $_dataTransaction->getSelect()->limit(1);
        
        return $_dataTransaction;
    }
    
}