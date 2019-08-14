<?php

/**
 *
 */
class Ydral_Correos_Block_Onepage_Progress extends Mage_Checkout_Block_Onepage_Progress
{
    
    public function escapeHtml($data, $allowedTags = null)
    {
        if (version_compare(Mage::getVersion(), '1.7.0', '>=')) {
            return $data;
        } else {
            return parent::escapeHtml($data, $allowedTags);
        }
    }
    
}
