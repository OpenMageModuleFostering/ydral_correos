<?php

/**
 *
 */
class Ydral_Correos_Block_Adminhtml_Sales_Order_View_Tab_Info_Correosinter extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
    public function showInfo($_order)
    {
        $htmlTmp = '';
        $htmlTmp .= $this->helper('correos')->__('Paquete Postal Internacional Prioritario');
        
        return $htmlTmp;
    }
}
