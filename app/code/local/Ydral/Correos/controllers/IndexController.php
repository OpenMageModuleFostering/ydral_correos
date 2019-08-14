<?php

/** 
 *
 */
class Ydral_Correos_IndexController extends Mage_Core_Controller_Front_Action 
{

	public function getphoneAction()
	{
	     $this->getResponse()->setBody(Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getTelephone());
	}

}