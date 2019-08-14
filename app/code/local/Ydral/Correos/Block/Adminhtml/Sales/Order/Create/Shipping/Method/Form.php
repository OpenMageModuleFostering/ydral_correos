<?php

/**
 *
 */
class Ydral_Correos_Block_Adminhtml_Sales_Order_Create_Shipping_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function escapeHtml($data, $allowedTags = null)
    {
        return $data;
    }
    
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/shippingchange');
    }
}
