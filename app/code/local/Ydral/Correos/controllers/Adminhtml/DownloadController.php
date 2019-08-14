<?php

/** 
 *
 */
class Ydral_Correos_Adminhtml_DownloadController extends Mage_Adminhtml_Controller_Action 
{


	public function getphoneAction()
	{
	     $this->getResponse()->setBody(Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getTelephone());
	}

	
	/**
	 *
	 */
    public function downloadEtiquetaAction()
    {
        
        $etiquetaId = $this->getRequest()->getParam('etiqueta', false);
        $orderId = $this->getRequest()->getParam('order', false);

        
        if ($etiquetaId && $orderId) 
        {
            $dataPdf = Mage::getModel('correos/shipment')->getEtiquetaRemote($orderId, $etiquetaId); 
            if (!$dataPdf)
            {
                $this->_getSession()->addError(Mage::helper('correos')->__('No se han encontrado pedidos para imprimir.'));
                Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$orderId)));
                return;
            }
            if (Mage::helper('correos')->getValueConfig('savepdf', 'opciones'))
            {
                //  guardamos el local
                $this->_preparePdf($dataPdf[0]->Etiqueta->Etiqueta_pdf->NombreF, base64_decode($dataPdf[0]->Etiqueta->Etiqueta_pdf->Fichero), 'application/pdf');
            } else {
                //  peticion en real
                $this->_prepareDownloadResponse($dataPdf[0]->Etiqueta->Etiqueta_pdf->NombreF, base64_decode($dataPdf[0]->Etiqueta->Etiqueta_pdf->Fichero), 'application/pdf');
            }
        }
        
    }
    
    
    /**
	 *
	 */
    public function downloadDuaAction()
    {
        

        
        $etiquetaId = $this->getRequest()->getParam('etiqueta', false);
        $orderId = $this->getRequest()->getParam('order', false);
        $bultos  = (int)$this->getRequest()->getParam('dua_bultos', false);
        $doc_dua = $this->getRequest()->getParam('doc_dua', false);
        
        if (!is_numeric($bultos))
        {
            $this->_getSession()->addError(Mage::helper('correos')->__('Número de bultos incorrecto.'));
            Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$orderId)));
            return;
        }
        
        if (empty($doc_dua))
        {
            $this->_getSession()->addError(Mage::helper('correos')->__('Es necesario elegir un tipo de documento.'));
            Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$orderId)));
            return;
        }

        if ($etiquetaId && $orderId) 
        {
            if ($doc_dua == 'dcaf')
            {
                $dataPdf = Mage::getModel('correos/shipment')->getDocumentDua($orderId, $bultos, 'DCAF'); 
                if ($dataPdf === false)
                {
                    $this->_getSession()->addError(Mage::helper('correos')->__('No se han encontrado pedidos para imprimir.'));
                    Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$orderId)));
                    return;
                }
                $this->_preparePdf('dua_doc_' . time() . '.pdf', base64_decode($dataPdf), 'application/pdf');
            } 
            if ($doc_dua == 'ddp')
            {
                $dataPdf = Mage::getModel('correos/shipment')->getDocumentDua($orderId, $bultos, 'DDP'); 
                if ($dataPdf === false)
                {
                    $this->_getSession()->addError(Mage::helper('correos')->__('No se han encontrado pedidos para imprimir.'));
                    Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$orderId)));
                    return;
                }
                $this->_preparePdf('ddp_doc_' . time() . '.pdf', base64_decode($dataPdf), 'application/pdf');
            }
        }
        
    }
    
    
    /**
     *
     */
    public function downloadCn23cp71Action()
    {        
        $etiquetaId = $this->getRequest()->getParam('etiqueta', false);
        $orderId = $this->getRequest()->getParam('order', false);

        if ($etiquetaId && $orderId) 
        {
            $dataPdf = Mage::getModel('correos/shipment')->getDocumentCn23cp71($orderId, $etiquetaId); 
            if ($dataPdf === false)
            {
                $this->_getSession()->addError(Mage::helper('correos')->__('No se ha podido descargar la documentación.'));
                Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$orderId)));
                return;
            }
            $this->_preparePdf('cn23cp71_doc_' . time() . '.pdf', base64_decode($dataPdf), 'application/pdf');
        }
        
    }
    
    
    /**
     *
     */
    protected function _preparePdf($fileName, $content, $contentType = 'application/octet-stream', $contentLength = null) 
    {
        
        /**
         *  check
         */ 
        $session = Mage::getSingleton('admin/session');
        if ($session->isFirstPageAfterLogin()) {
            $this->_redirect($session->getUser()->getStartupPageUrl());
            return $this;
        }
        
        if (empty($content))
        {
            $this->_getSession()->addError(Mage::helper('correos')->__('El servidor de Correos ha devuelto una respuesta vacía.'));
            return;
        }


        /**
         *  guardamos un fichero temporal del pdf
         */
        $ioAdapter  = new Varien_Io_File();
        $path       = Mage::getBaseDir() . DS . 'var' . DS . 'pdf';
        $file       = $path . DS . $fileName;
        if (!$ioAdapter->fileExists($file)) 
        {
            if (!file_exists($path))
            {
                $importReadyDirResult = $ioAdapter->mkdir($path);
                if (!$importReadyDirResult) 
                {
                    Mage::throwException(Mage::helper('correos')->__('Directorio no accesible.'));
                }
            }

            if (!$ioAdapter->write($file, $content)) 
            {
                Mage::throwException(Mage::helper('correos')->__('No se ha podido guardar el contenido del PDF en el fichero temporal.'));
            }
        }

        
        /**
         *  salida
         */
        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', strlen($content))
            ->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"')
            ->setHeader('Last-Modified', date('r'));

        
        /**
         *  enviamos el fichero
         */
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        $ioAdapter->open(array('path' => $ioAdapter->dirname($file)));
        $ioAdapter->streamOpen($file, 'r');
        while ($buffer = $ioAdapter->streamRead()) {
            print $buffer;
        }
        $ioAdapter->streamClose();

        exit(0);


        //$this->getResponse()->setBody($content);
        
        return $this;
    }
    
}