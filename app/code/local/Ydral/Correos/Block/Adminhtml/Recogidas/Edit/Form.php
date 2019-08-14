<?php


class Ydral_Correos_Block_Adminhtml_Recogidas_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     *
     */
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save'),
                'method' => 'post',
            )
        );
        

        /** ids mass actions **/        
        $orderIds = $this->getRequest()->getPost('order_ids');
        
        /** first order **/
        $actualOrderId = array_shift(array_values($orderIds));
        $_order = Mage::getModel('sales/order')->load($actualOrderId);
        
        if (!$_order) return false;
        $shippingAddress = $_order->getShippingAddress();
        $_dataRecogida = Mage::getModel('correos/recoger')->getCheckoutData('order', $_order->getRealOrderId())->getFirstItem();
        if ($_dataRecogida->getId() == '') 
        {
            Mage::throwException(
                Mage::helper('correos')->__('No hay datos asociados a este pedido de Correos.')
            );
            return false;
        }
        
 
        $form->setUseContainer(true);
        $this->setForm($form);
 
        $helper = Mage::helper('correos');
        
        $fieldset = $form->addFieldset('address', array(
            'legend' => $helper->__('Datos de dirección recogida'),
            'class' => 'fieldset-wide'
        ));
        
        $fieldset->addField('nombre', 'text', array(
            'name' => 'nombre',
            'label' => $helper->__('Nombre'),
            'required'  => true,
            //'value' => Mage::helper('correos')->getValueConfig('nombre', 'remitente'),
            'value' => $shippingAddress->getFirstname(),
            'after_element_html' => "
                                <script type=\"text/javascript\">
                                function checkRemitente() {
                                    if ( ($('empresa') && $('empresa').value != '') ||
                                         ($('nombre') && $('nombre').value != '' && $('apellidos') && $('apellidos').value != '' )
                                       )
                                    { 
                                       $('empresa').removeClassName('required-entry'); 
                                       $('nombre').removeClassName('required-entry'); 
                                       $('apellidos').removeClassName('required-entry'); 
                                    }
                                    else
                                    {
                                       $('empresa').addClassName('required-entry'); 
                                       $('nombre').addClassName('required-entry'); 
                                       $('apellidos').addClassName('required-entry'); 
                                    }
                                }
                                Event.observe(window, 'load', function() {
                                    checkRemitente();
                                    Event.observe('nombre', 'change', checkRemitente);
                                    Event.observe('apellidos', 'change', checkRemitente);
                                    Event.observe('empresa', 'change', checkRemitente);
                                });
                                </script>",
        ));
        
        $fieldset->addField('apellidos', 'text', array(
            'name' => 'apellidos',
            'label' => $helper->__('Apellidos'),
            'required'  => true,
            //'value' => Mage::helper('correos')->getValueConfig('apellidos', 'remitente'),
            'value' => $shippingAddress->getLastname(),
        ));
        
        $fieldset->addField('empresa', 'text', array(
            'name' => 'empresa',
            'label' => $helper->__('Empresa'),
            'required'  => false,
            //'value' => Mage::helper('correos')->getValueConfig('empresa', 'remitente'),
            'value' => $shippingAddress->getCompany(),
        ));
        
        $fieldset->addField('direccion', 'text', array(
            'name' => 'direccion',
            'label' => $helper->__('Dirección'),
            'required'  => true,
            //'value' => Mage::helper('correos')->getValueConfig('direccion', 'remitente'),
            'value' => $shippingAddress->getStreetFull(),
        ));
        
        $fieldset->addField('localidad', 'text', array(
            'name' => 'localidad',
            'label' => $helper->__('Localidad'),
            'required'  => true,
            //'value' => Mage::helper('correos')->getValueConfig('localidad', 'remitente'),
            'value' => $shippingAddress->getCity(),
        ));
        
        $fieldset->addField('cp', 'text', array(
            'name' => 'cp',
            'label' => $helper->__('Código Postal'),
            'required'  => true,
            //'value' => Mage::helper('correos')->getValueConfig('codpostal', 'remitente'),
            'value' => $shippingAddress->getPostcode(),
        ));
        
        //$region = Mage::getModel('directory/region')->load(Mage::helper('correos')->getValueConfig('region_id', 'remitente'));
        //if ($region) $provincia = $region->getName();
        //else $provincia = '';
        $fieldset->addField('provincia', 'text', array(
            'name' => 'provincia',
            'label' => $helper->__('Provincia'),
            'required'  => true,
            //'value' => $provincia,
            'value' => $shippingAddress->getRegion(),
        ));
        
        $fieldset->addField('telefono', 'text', array(
            'name' => 'telefono',
            'label' => $helper->__('Teléfono'),
            'required'  => true,
            //'value' => Mage::helper('correos')->getValueConfig('telefono', 'remitente'),
            'value' => $shippingAddress->getTelephone(),
        ));
        
        $fieldset->addField('movil', 'text', array(
            'name' => 'movil',
            'label' => $helper->__('Móvil'),
            'required'  => true,
            //'value' => Mage::helper('correos')->getValueConfig('telefonosms', 'remitente'),
            'value' => $_dataRecogida->getMovilAsociado(),
        ));
        
        $fieldset->addField('email', 'text', array(
            'name' => 'email',
            'label' => $helper->__('Email'),
            'required'  => true,
            //'value' => Mage::helper('correos')->getValueConfig('email', 'remitente'),
            'value' => $shippingAddress->getEmail(),
        ));   
        
        $fieldset = $form->addFieldset('display', array(
            'legend' => $helper->__('Información de recogida'),
            'class' => 'fieldset-wide'
        ));
 
        $fieldset->addField('bultos', 'text', array(
            'name' => 'bultos',
            'label' => $helper->__('Número de bultos'),
            'required'  => true,
        ));
        
        $fieldset->addField('horario', 'text', array(
            'name' => 'horario',
            'label' => $helper->__('Horario de recogida'),
            'after_element_html' => '<br /><small>(Ejemplo: 10:00-12:00)</small>',
            'required'  => true,
        ));
        
        $fieldset->addField('recogida', 'date', array(
            'name'               => 'recogida',
            'label'              => Mage::helper('correos')->__('Fecha Recogida'),
            'image'              => $this->getSkinUrl('images/grid-cal.gif'),
            'format'             => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM) ,
            'value'              => date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                                          strtotime('tomorrow') ),
            'required'  => true,
        ));
        
        $fieldset->addField('observaciones', 'textarea', array(
            'name' => 'observaciones',
            'label' => $helper->__('Observaciones'),
        ));
        
        $fieldset->addField('orderids', 'hidden', array(
            'name' => 'orderids',
            'value' => implode(",", $orderIds),
        ));
        
        /*
        $fieldset->addField('submit', 'submit', array(
          'required'  => true,
          'value'  => 'Solicitar recogida',
        ));
        */
        
 
        if (Mage::registry('correos_adminform')) {
            $form->setValues(Mage::registry('correos_adminform')->getData());
        }
 
        return parent::_prepareForm();

    }


}