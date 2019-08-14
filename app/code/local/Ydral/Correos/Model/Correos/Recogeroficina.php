<?php

/**
 *  RECOGEROFICINA
 */
class Ydral_Correos_Model_Correos_Recogeroficina extends Mage_Shipping_Model_Carrier_Abstract
{
    
    public $_codigoPostal = '';
    protected $_code;  
    protected $_default_condition_name = 'package_weight';
    protected $_shipping_free = false; 
    protected $_methodImage;
    
    protected $_conditionNames = array();


    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        foreach ($this->getCode('condition_name') as $k=>$v) {
            $this->_conditionNames[] = $k;
        }
    }


	/**
	 *
	 */
	public function isTrackingAvailable()
	{
		return true;
	}
	
	
    /**
     *
     * @return array
     */
    public function getAllowedMethods() 
    {
        return array($this->_code => $this->getConfigData('name'));
    }
        
    
    /**
     *
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {

        $err = null;
        $envio = array();
        

        if (!$this->getConfigFlag('active')) 
        { 
		    return false;
		}
        
		
        /**
         *  Excluye productos el precio de los productos virtuales
         */
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) 
        {
            foreach ($request->getAllItems() as $item) 
            {
                if ($item->getParentItem()) 
                {
                    continue;
                }
                
                if ($item->getHasChildren() && $item->isShipSeparately()) 
                {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) 
                        {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }
		
		
        /**
         *  Free Shipping
         */
        $freeQty = 0;
        if ($request->getAllItems()) 
        {
            foreach ($request->getAllItems() as $item) 
            {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeQty += $item->getQty() * ($child->getQty() - (is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0));
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeQty += ($item->getQty() - (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0));
                }
            }
        }
        $shippingFreePrice = $this->getConfigData('shippingfree');
		if (is_numeric($shippingFreePrice) && ($shippingFreePrice > 0) && ($shippingFreePrice <= $request->getPackageValue()))
		{
		    $this->_shipping_free = true;
		}
        

        /**
         *  package_weight
         *  package_value
         *  package_qty
         */
        $request->setConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
        
        
        /**
         *  Peso del paqueta y cant. envio gratuito
         */
        $oldWeight  = $request->getPackageWeight();
        $oldQty     = $request->getPackageQty();
        
        
        //  Max peso
        $pesoMaximo = $this->getConfigData('max_weight');
        if ($oldWeight > $pesoMaximo) return false;
        
        
        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);
        $request->setMethodCode($this->_code);   //  compatibilidad

        $result = Mage::getModel('shipping/rate_result');
        $rate = $this->getRate($request);
        
        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);
        
        
        /**
        *
        */
        if (!$request->getOrig()) 
        {
	        $request->setCountryId(Mage::getStoreConfig('shipping/origin/country_id', $request->getStore()))
	                ->setPostcode(Mage::getStoreConfig('shipping/origin/postcode', $request->getStore()));
	    }
	    
	    
        /**
         *
         */
        /*
        $_datosEnvio['origen']  = Mage::getStoreConfig('shipping/origin/postcode', $request->getStore());
        
        $_datosEnvio['destino'] = $request->getDestPostcode();
        $_datosEnvio['peso']    = $request->getPackageWeight();
        $_datosEnvio['bultos']  = 1;
        $_datosEnvio['precioProducto'] = $request->getPackageValue();
        */
        	    
	    
        /**
         *
         */
        if (!empty($rate) && $rate['price'] >= 0) 
        {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            //$method->setMethodTitle($this->getConfigData('name'));
            $method->setMethodTitle('<img src="'.Mage::getModel('core/design_package')->getSkinUrl('correos/images/'.$this->_methodImage).'" alt="" title="" /><span class="title_correos_method">&nbsp;'.$this->getConfigData('name').'</span>');

            if ($request->getFreeShipping() === true || ($request->getPackageQty() == $freeQty)) 
            {
                $shippingPrice = 0;
            } elseif ($this->_shipping_free === true) {
                $shippingPrice = 0;
            } else {
                //$shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
                $shippingPrice = $rate['price'];
            }

            $method->setPrice($shippingPrice);
            $method->setCost($this->getConfigData('cost'));

            $result->append($method);
        }
        
        //$this->setCodigoPostal($request->getDestPostcode());

        return $result;
        
	}
	
	
	
	/*
	public function setCodigoPostal($codPostal)
	{
	    $this->_codigoPostal = $codPostal;
	}
	public function getCodigoPostal()
	{
	    return $this->_codigoPostal;
	}
	*/

	
	/**
	 *
	 */
	public function getRate(Mage_Shipping_Model_Rate_Request $request)
	{
		return Mage::getResourceModel('correos/carrier_correos')->getRate($request);
	}


    /**
     *
     */
    public function getCode($type, $code='')
    {
        $codes = array(

            'condition_name'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight vs. Destination'),
                'package_value'  => Mage::helper('shipping')->__('Price vs. Destination'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items vs. Destination'),
            ),

            'condition_name_short'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight (and above)'),
                'package_value'  => Mage::helper('shipping')->__('Order Subtotal (and above)'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items (and above)'),
            ),

        );

        if (!isset($codes[$type])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Table Rate code type: %s', $type));
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Table Rate code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];
    }
    
}