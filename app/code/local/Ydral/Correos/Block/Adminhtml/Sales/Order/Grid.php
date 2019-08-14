<?php

/**
 *
 */
class Ydral_Correos_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{

    /**
     *
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $statuses = Mage::getSingleton('correos/pdfmerger')->getOptionArray();
        array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('print_preregistro', array(
             'label'=> Mage::helper('correos')->__('Imprimir preregistro A4'),
             'url'  => $this->getUrl('*/adminhtml_pdf/multiplePrintA4'),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'position',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Pos. inicial'),
                         'values' => $statuses
                     )
             )
        ));

		$this->getMassactionBlock()->addItem('print_preregistro_etiq', array(
             'label'=> Mage::helper('correos')->__('Imprimir preregistro Etiquetadora'),
             'url'  => $this->getUrl('*/adminhtml_pdf/multiplePrintEt'),
        ));	
		$this->getMassactionBlock()->addItem('pedir_recogida', array(
             'label'=> Mage::helper('correos')->__('Ordenar recogida'),
             'url'  => $this->getUrl('*/adminhtml_mail/recogida'),
        ));	
		$this->getMassactionBlock()->addItem('generar_manifiesto', array(
             'label'=> Mage::helper('correos')->__('Generar manifiesto'),
             'url'  => $this->getUrl('*/adminhtml_pdf/manifiesto'),
        ));	
        $this->getMassactionBlock()->addItem('exportar_pedidos', array(
             'label'=> Mage::helper('correos')->__('Exportar pedidos Correos (Excel)'),
             'url'  => $this->getUrl('*/adminhtml_pedidos/exportarexcel'),
        ));	
        $this->getMassactionBlock()->addItem('exportar_pedidos_txt', array(
             'label'=> Mage::helper('correos')->__('Exportar pedidos Correos (Txt)'),
             'url'  => $this->getUrl('*/adminhtml_pedidos/exportartxt'),
        ));
        $this->getMassactionBlock()->addItem('devolucion_correos', array(
             'label'=> Mage::helper('correos')->__('Devolución Correos'),
             'url'  => $this->getUrl('*/adminhtml_pdf/devolucion'),
        ));	
        return $this;
    }

}