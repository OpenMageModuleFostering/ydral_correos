<?php

/** 
 *
 */
class Ydral_Correos_Adminhtml_ShipController extends Mage_Adminhtml_Controller_Action 
{
    
    public function shippingAction()
    {
		$orderId     		= $this->getRequest()->getParam('order_id');
		$_order = Mage::getModel('sales/order')->load($orderId);
		Mage::register('shipping_order', $_order);
		if ($_order)
		{
		    
		    $storeId = $_order->getStoreId();
		    $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

            $zipcode = $_order->getShippingAddress()->getPostcode();
            $country = $_order->getShippingAddress()->getCountryId();

            /*
            $cart = Mage::getSingleton('checkout/cart');
            $address = $cart->getQuote()->getShippingAddress();
            $address
                    ->setCountryId($country)
                    ->setPostcode($zipcode)
                    ->setCollectShippingRates(true);
            $cart->save();

            $rates = $address->collectShippingRates()->getGroupedAllShippingRates();
            */
            
            $_quoteId = $_order->getQuoteId();
            $quote = Mage::getModel('sales/quote')->load($_quoteId);
            $quote->getShippingAddress()->collectTotals();
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $rates = $quote->getShippingAddress()->collectShippingRates()->getGroupedAllShippingRates();

            //$rates = $quote->getShippingAddress()->getShippingRatesCollection();
            
            Mage::register('admin_rates', $rates);
            
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
		    
            $this->loadLayout('popup');
            $this->renderLayout();
        }
    }
    
    
    /**
     *
     */
    public function shippingchangeAction()
    {
        
        $params     = $this->getRequest()->getParams();

        try
        {
                    
    		$orderId    = $this->getRequest()->getParam('orderid');
    		$order     = Mage::getModel('sales/order')->load($orderId);
    		
    		if ( empty($params['shipping_method']) || !in_array($params['shipping_method'], Mage::helper('correos')->getAllowedMethods()) )
    		{
    		    Mage::throwException(Mage::helper('correos')->__('Error en el método seleccionado.'));
    		}
    		
    		if ($order)
    		{
             
                $_dataSave = array();
             
                //   
                if(!empty($params['oficinas_correos_content_select']))
                {
                    $idSelector = trim(strip_tags($params['oficinas_correos_content_select']));
                    $_dataSave['correos_recogida'] = $idSelector;
                }
                if(!empty($params['cp_search']))
                {
                    $cpField = trim(strip_tags($params['cp_search']));
                    $_dataSave['correos_oficina'] = $cpField;
                }
                if(!empty($params['phone_correos']))
                {
                    $phoneField = addslashes(trim(strip_tags($params['phone_correos'])));
                    $_dataSave['movil_asociado'] = $phoneField;
                }
                if(!empty($params['correos_email']))
                {
                    $emailField = addslashes(trim(strip_tags($params['correos_email'])));
                    $_dataSave['email'] = $emailField;
                }
                if(!empty($params['selectedpaq_code']))
                {
                    $idHomepaq = Mage::helper('core')->escapeHtml(trim($params['selectedpaq_code']));
                    $_dataSave['homepaq_id'] = $idHomepaq;
                }
                if(!empty($params['homepaq_token']))
                {
                    $token = Mage::helper('core')->escapeHtml(trim($params['homepaq_token']));
                    $_dataSave['token'] = $token;
                }
                if(!empty($params['selectedpaq_data']))
                {
                    $info_punto = Mage::helper('core')->escapeHtml(trim($params['selectedpaq_data']));
                    $_dataSave['info_punto'] = $info_punto;
                }
                if(!empty($params['correos_mobile_lang']))
                {
                    $idioma_sms = Mage::helper('core')->escapeHtml(trim($params['correos_mobile_lang']));
                    $_dataSave['language'] = $idioma_sms;
                }
                if(!empty($params['cr_timetable']))
                {
                    $horarioField = addslashes(trim(strip_tags($params['cr_timetable'])));
                    $_dataSave['horario'] = $horarioField;
                }
            
                $idOrder    = $order->getIncrementId();
                $_dataTransaction = Mage::getModel('correos/recoger')
                        ->getCheckoutData('order', $idOrder);
    

                if (count($_dataTransaction) == 0)
                {
                    
            		$_dataSave = array(
            		    'entity_type'       => 'order',
            		    'checkout_id'       => $idOrder,
            		);
                    
                    $quoteTransaction = Mage::getModel('correos/recoger')->setData($_dataSave);
                    $quoteTransaction->save();
                } else {
                    $_data = $_dataTransaction->getFirstItem();
                    $idTransaction = $_data->getId();
                    $orderTransaction = Mage::getModel('correos/recoger')->load($idTransaction);
                    $orderTransaction->addData($_dataSave);
                    $orderTransaction->save();
                }
                
                
                $newMethodCode = current(explode('_', $params['shipping_method']));
                $shippingDescription = Mage::getStoreConfig('carriers/' . $newMethodCode . '/title');
                $order->setShippingDescription($shippingDescription);
                $order->setShippingMethod($params['shipping_method']);
                $order->save();
		        
		        
		        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('correos')->__('Método correctamente modificado.'));
               		    
                
            }
        
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        
        
        /*
        $messages = Mage::getSingleton('adminhtml/session')->getMessages(true);
        foreach($messages->getItems() as $message)
        {  
           echo $message->getText();
        }
        */
        
        echo '<script type="text/javascript">parent.jQuery.fancybox.close();</script>';

        
    }

}