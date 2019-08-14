<?php

/**
 *
 */
class Ydral_Correos_Block_Adminhtml_Sales_Order_View_Tab_Info_Recogeroficina extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
    public function showInfo($_order)
    {
        $htmlTmp = '';
        $_dataRecogida = Mage::getModel('correos/recoger')->getCheckoutData('order', $_order->getRealOrderId());
        
        if (count($_dataRecogida))
        {
            
            $_data = $_dataRecogida->getFirstItem();
            if ($_data->getCorreosOficina() == '')
            {
                $codPostalRecogida = $_order->getShippingAddress()->getPostcode();
            } else {
                $codPostalRecogida = $_data->getCorreosOficina();
            }

            $_dataOficina = Mage::getModel('correos/oficinas')->dataOficinas($_data->getCorreosRecogida(), $codPostalRecogida);
            if (is_array($_dataOficina))
            {
                $htmlTmp .= $this->helper('correos')->__('Envío a la oficina: %s', '');
                $htmlTmp .= $this->helper('correos')->__('Oficina <strong>%s</strong> - %s-%s (%s)', $_dataOficina['nombre'], $_dataOficina['direccion'], $_dataOficina['localidad'], $_dataOficina['cp']);
            } else {
                $htmlTmp .= $this->helper('correos')->__('Envío a la oficina: %s', $_data->getCorreosRecogida());
            }
            
        }
        
        return $htmlTmp;
    }
}
