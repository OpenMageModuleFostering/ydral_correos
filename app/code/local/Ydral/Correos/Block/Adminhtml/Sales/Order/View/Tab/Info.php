<?php

/**
 *
 */
class Ydral_Correos_Block_Adminhtml_Sales_Order_View_Tab_Info extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{

    /**
     *
     */
    public function getGiftOptionsHtml()
    {
        
        $html = '';
        $_order = $this->getSource();
        $_envioCorreos = false;

        if (in_array($_order->getShippingMethod(), Mage::helper('correos')->getAllowedMethods()))
        {
            $_envioCorreos = true;
        }

        if ($_envioCorreos)
        {

            $htmlTmp = '';
            $html .= '<div id="customer_comment" class="giftmessage-whole-order-container"><div class="entry-edit">';
            $html .= '<div class="entry-edit-head"><h4>'.$this->helper('correos')->__('Información del envío con Correos').'</h4></div>';
            $html .= '<fieldset>';
            
            
            $_method = current(explode('_', $_order->getShippingMethod())); 
            
            
            /**
             *  Info
             *  Ydral_Correos_Block_Adminhtml_Sales_Order_View_Tab_Info_X
             */
            $block = Mage::app()->getLayout()->createBlock('correos/adminhtml_sales_order_view_tab_info_' . $_method);
            if ($block) {
                $htmlTmp .= $block->showInfo($_order);
            }
            
            
            /**
             *  Tracking
             */
            $tracks = $_order->getTracksCollection();
            $tracksValidos = Mage::helper('correos')->getTracksValidos();
            foreach ($tracks as $track)
            {
                if (in_array($track->getCarrierCode(), $tracksValidos))
                {
                    $htmlTmp .= '<br /><a href="' . Mage::helper("adminhtml")->getUrl("*/adminhtml_download/downloadEtiqueta", array('order' => $_order->getId(), 'etiqueta' => $track['number'])) . '">' . $this->helper('correos')->__('Imprimir etiqueta de envío.') . '</a>';
                    $htmlTmp .= '<br /><b>' . $this->helper('correos')->__('Estado del env&iacute;o:') . '</b>' . Mage::getModel('correos/seguimiento')->getLastStateEnvioFases($track['number']);
                    if ($this->helper('correos/dua')->isDua($_order) || ($this->helper('correos/dua')->isCn23cp71($_order)))
                    {
                        
                        $htmlTmp .= '<br /><br /><div><strong>' . $this->helper('correos')->__('Documentaci&oacute;n aduanera:') . '</strong>';
                        
                        if ($this->helper('correos/dua')->isDua($_order))
                        {
                            $htmlTmp .= '<br /><form id="dua_form" action="' . Mage::helper("adminhtml")->getUrl("*/adminhtml_download/downloadDua", array('order' => $_order->getId(), 'etiqueta' => $track['number'])) . '">
                                                <input type="hidden" id="order_id" name="order_id" value="' . $_order->getId() .'">
                                                <input type="hidden" id="etiqueta" name="etiqueta" value="' . $track['number'] .'">
                                                <table cellspacing="10">
                                                    <tbody>
                                                        <tr>
                                                            <td class="label"><label>' . $this->helper('correos')->__('Número de bultos:') . '</label></td>
                                                            <td class="input-ele"><input class="" type="text" id="dua_bultos" name="dua_bultos" value="1"></td>
                                                            <td class=""><input type="radio" id="doc_dcaf" name="doc_dua" value="dcaf" /><label for="doc_dcaf">' . $this->helper('correos')->__('DUA') . '</label></td>
                                                            <td class=""><input type="radio" id="doc_ddp" name="doc_dua" value="ddp" /><label for="doc_ddp">' . $this->helper('correos')->__('DDP') . '</label></td>
                                                            <td><button type="submit" class="scalable save"><span>' . $this->helper('correos')->__('Solicitar documentación') . '</span></button></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </form>';
                        }
                        if ($this->helper('correos/dua')->isCn23cp71($_order))
                        {
                            $htmlTmp .= '<br /><form id="dua_form" action="' . Mage::helper("adminhtml")->getUrl("*/adminhtml_download/downloadCn23cp71", array('order' => $_order->getId(), 'etiqueta' => $track['number'])) . '">
                                                <input type="hidden" id="order_id" name="order_id" value="' . $_order->getId() .'">
                                                <input type="hidden" id="etiqueta" name="etiqueta" value="' . $track['number'] .'">
                                                <table cellspacing="10">
                                                    <tbody>
                                                        <tr>
                                                            <td class="label"><label>' . $this->helper('correos')->__('Declaración de contenidos:') . '</label></td>
                                                            <td class="input-ele">&nbsp;</td>
                                                            <td class="">&nbsp;</td>
                                                            <td class="">&nbsp;</td>
                                                            <td><button type="submit" class="scalable save"><span>' . $this->helper('correos')->__('Solicitar documentación') . '</span></button></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </form>';
                        }
                        $htmlTmp .= '</div>';
                    }
                }
                
                
                /**
                 *  RMA
                 */
                if ($track->getCarrierCode() == 'correos_rma')
                {
                    $htmlTmp .= '<br /><br /><a href="' . Mage::helper("adminhtml")->getUrl("*/adminhtml_download/downloadEtiqueta", array('order' => $_order->getId(), 'etiqueta' => $track['number'])) . '">' . $this->helper('correos')->__('Imprimir etiqueta de devolución.') . '</a><br />';
                }
            }
            
            
            /**
             *
             */  
            if ($_order->getTracksCollection()->count())
            {
                $htmlTmp .= '<br /><a href="#" id="linkId" onclick="popWin(\'' . $this->helper('correos')->getTrackingPopupUrlBySalesModel($_order) . '\',\'trackorder\',\'width=800,height=600,resizable=yes,scrollbars=yes\')" title="' . $this->helper('correos')->__('Seguimiento del pedido') . '">' . $this->helper('correos')->__('Seguimiento del pedido') . '</a>';
            }
            
            
            /**
             *  Empty
             */
            if (empty($htmlTmp))
            {
                $htmlTmp = $this->helper('correos')->__('A&uacute;n no hay informaci&oacute;n disponible');
            }
            
            
            
            
            
            $html .= $htmlTmp;
            $html .= '</fieldset>';
            $html .= '</div></div><br />';        

            
        }
        
        $giftHtml = parent::getGiftOptionsHtml();
        
        return $html . $giftHtml;
        
    }
    
    
    public function escapeHtml($data, $allowedTags = null)
    {
        if (version_compare(Mage::getVersion(), '1.7.0', '>=')) {
            return strip_tags($data);
        } else {
            return parent::escapeHtml($data, $allowedTags);
        }
    }

}