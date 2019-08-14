<?php

/**
 *
 */
class Ydral_Correos_Block_Adminhtml_Recogidas_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
    * Constructor
    */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'correos';
        $this->_controller = 'adminhtml_recogidas';
        $this->_headerText = Mage::helper('correos')->__('Datos recogida');

        $this->_updateButton('save', 'label','Solicitar recogida');
    }

}
