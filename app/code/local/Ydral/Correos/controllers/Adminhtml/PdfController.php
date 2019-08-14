<?php

/** 
 *
 */
class Ydral_Correos_Adminhtml_PdfController extends Mage_Adminhtml_Controller_Action 
{

    public function multiplePrintEtAction()
    {
        $this->multiplePrint('Etiquetadora');
    }
    public function multiplePrintA4Action()
    {
        $this->multiplePrint('A4');
    }
    public function devolucionAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        
        if (count($orderIds) == 1)
        {
            $this->multiplePrint('Etiquetadora', 'devolucion');
        } else {
            $this->multiplePrint('A4', 'devolucion');
        }
    }

    /**
     * Export shipping table rates in csv format
     *
     */
    protected function multiplePrint($size, $typeDoc = '')
    {
        
        $files = array();        
        try
        {
            $orderIds = $this->getRequest()->getPost('order_ids');
            $position = $this->getRequest()->getPost('position');

            if (!empty($orderIds))
            {
                /**
                 *
                 */
                $fileNameFinal = 'ordersPdf';
                foreach ($orderIds as $orderId) 
                {
                                        
                    $_order = Mage::getModel('sales/order')->load($orderId);					
                	if($_order)
                	{
                	    
                        $tracks = $_order->getTracksCollection();
                        $tracksValidos = Mage::helper('correos')->getTracksValidos();

                        foreach ($tracks as $track)
                        {                        
                            if (in_array($track->getCarrierCode(), $tracksValidos))
                            {
                                switch ($typeDoc)
                                {
                                    case 'devolucion':
                                        $dataPdf = Mage::getModel('correos/shipment')->logisticaInversa($_order, false);
                                        $fileName = $dataPdf[0]->Etiqueta->Etiqueta_pdf->NombreF;
                                        if ($this->_preparePdf($fileName, base64_decode($dataPdf[0]->Etiqueta->Etiqueta_pdf->Fichero), 'application/pdf'))
                                        {
                                            $fileNameFinal .= '_' . $_order->getId();
                                            $files[] = Mage::getBaseDir() . DS . 'var' . DS . 'pdf' . DS . $fileName;
                                        } else {
                                            $this->_getSession()->addError(Mage::helper('correos')->__('El pedido %s no se ha podido imprimir.', $_order->getRealOrderId()));
                                        }
                                        break;
                                        
                                        
                                    default:
                                
                                        $dataPdf = Mage::getModel('correos/shipment')->getEtiquetaRemote($_order->getId(), $track['number']);
                                        $fileName = $dataPdf[0]->Etiqueta->Etiqueta_pdf->NombreF;
                                        if ($this->_preparePdf($fileName, base64_decode($dataPdf[0]->Etiqueta->Etiqueta_pdf->Fichero), 'application/pdf'))
                                        {
                                            $fileNameFinal .= '_' . $_order->getId();
                                            $files[] = Mage::getBaseDir() . DS . 'var' . DS . 'pdf' . DS . $fileName;
                                        } else {
                                            $this->_getSession()->addError(Mage::helper('correos')->__('El pedido %s no se ha podido imprimir.', $_order->getRealOrderId()));
                                        }
                                        
                                }
                                
                            }
                        }
                	    
                	}
                }
                
                /**
                 *
                 */
                if (count($files) > 0)
                {
                    $pdfFinal = Mage::getModel('correos/pdfmerger');
                    if (isset($position) && ($position > 1 && $position < 5))
                    {
                        for ($p = 1; $p < $position; $p++)
                        {
                            $pdfFinal->addPDF(Mage::getBaseDir() . DS . 'var' . DS . 'pdf' . DS . 'empty.pdf', 'all');
                        }   
                    }
                    foreach ($files as $file) 
                    {
                        $pdfFinal->addPDF($file, 'all');
                    }
                    
                    $fileNameMerged = Mage::getBaseDir() . DS . 'var' . DS . 'pdf' . DS . $fileNameFinal . '.pdf';
                    $pdfFinal->merge('file', $fileNameMerged, $size);
                    

                    /**
                     *  salida
                     */
                    $this->getResponse()
                        ->setHttpResponseCode(200)
                        ->setHeader('Pragma', 'public', true)
                        ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                        ->setHeader('Content-type', 'application/pdf', true)
                        ->setHeader('Content-Disposition', 'attachment; filename="'.$fileNameFinal . '.pdf"')
                        ->setHeader('Last-Modified', date('r'));
            
                    
                    /**
                     *  enviamos el fichero
                     */
                    $ioAdapter  = new Varien_Io_File();
                    $this->getResponse()->clearBody();
                    $this->getResponse()->sendHeaders();
                    $ioAdapter->open(array('path' => $ioAdapter->dirname($fileNameMerged)));
                    $ioAdapter->streamOpen($fileNameMerged, 'r');
                    while ($buffer = $ioAdapter->streamRead()) {
                        print $buffer;
                    }
                    $ioAdapter->streamClose();
            
                    exit(0);                   
                    
                } else {
                    $this->_getSession()->addError($this->__('No se han encontrado ficheros de PDF para imprimir.'));
                }
                
            } else {
                $this->_getSession()->addError($this->__('No se han encontrado pedidos para imprimir.'));
            }
            
        } catch (Exception $e) {
            if (!$e->getMessage())
            {
                //Mage::throwException(Mage::helper('correos')->__($this->__('No se han encontrado pedidos para imprimir.')));
                $this->_getSession()->addError($this->__('No se han encontrado pedidos para imprimir.'));
            } else {
                //Mage::throwException($e->getMessage());
                $this->_getSession()->addError($e->getMessage());
            }
        }  
        
        $this->_redirectReferer(); 
        //$this->_redirect("adminhtml/sales_order/index");
        return;
        
    }
    
    
    
    /**
     *
     */
    public function manifiestoAction()
    {
        $files = array();
        
        try
        {
        
            $orderIds = $this->getRequest()->getPost('order_ids');
            if (!empty($orderIds))
            {
                
                /**
                 *
                 */
                $fileNameFinal = 'manifiestoPdf';
                $total_packages = 0;
			    $total_weight = 0;
			    $total_cashondelivery = 0;
			    $total_insurance = 0;
			    $correos_methods = Mage::helper('correos')->getMethods();
			    $importeSeguro = Mage::helper('correos')->getValueConfig('importeseguro', 'paquete');
			    $records = '';
			    if (empty($importeSeguro)) $importeSeguro = 0;
			    
			    $cr_carriers = array();
			    foreach(Mage::helper('correos')->getAllowedMethods() as $cr_carrier)
			    {
				    $cr_carriers[$cr_carrier] = array('title' => $correos_methods[$cr_carrier], 'total_packages' => 0, 'total_weight' => 0, 'total_cashondelivery' => 0, 'total_insurance' => 0);
				}
			
			
			    /**
			     *  
			     */
                foreach ($orderIds as $orderId) 
                {
                    $_order = Mage::getModel('sales/order')->load($orderId);					
                	if($_order)
                	{
                        $tracks = $_order->getTracksCollection();
                        $tracksValidos = Mage::helper('correos')->getTracksValidos();
                        
                        $cashondelivery = 0;
                        if ( ($_order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery')
                             || ($_order->getPayment()->getMethodInstance()->getCode() == 'ig_cashondelivery')
                             || ($_order->getPayment()->getMethodInstance()->getCode() == 'phoenix_cashondelivery')
                             || ($_order->getPayment()->getMethodInstance()->getCode() == 'msp_cashondelivery')
                             || ($_order->getPayment()->getMethodInstance()->getCode() == 'magegaga_cashondelivery')
                            )
                        {
                            $cashondelivery = $_order->getBaseGrandTotal();
                            $cashondelivery = number_format($cashondelivery, 2, '.', '');
                        }
                        foreach ($tracks as $track)
                        {
                            if (in_array($track->getCarrierCode(), $tracksValidos))
                            {
                                
                                $carrier_code = $_order->shippingMethod;
                                $carrier_name = $correos_methods[$_order->shippingMethod];
                                $order_collection   = Mage::getModel('correos/registro')->loadByCorreosId($track['number']);
                                
                                $peso_paquete = 0;
                                if ($order_collection)
                                {
                                    //  pesos registrados
                                    foreach ($order_collection as $order_info)
                                    {
                                        $peso_paquete += number_format($order_info->getPeso() / 1000, 3, '.', '');
                                    }
                                    if ($peso_paquete == 0) { $peso_paquete = 1; }
                                } else {
                                    $peso_paquete = Mage::helper('correos')->getPesoPaquete($_order, 'kg');
                                }
                                $records .= '<table class="records"><tr>' .
					                '<td style="width:150px;">' . $track['number'] . '</td>' .
					                '<td style="width:240px;">' . $_order->getShippingAddress()->getFirstname().' '.$_order->getShippingAddress()->getLastname().
					                '<br/>'.
					                $_order->getShippingAddress()->getStreetFull() . ' ' . Mage::helper('correos')->getCodigoPostal($_order) . ' ' . $_order->getShippingAddress()->getCity() . '</td>' .
					                '<td style="width:50px;">1</td>' .
					                '<td style="width:50px;">' . $peso_paquete . '</td>' .
					                '<td style="width:50px;">' . $cashondelivery . '&euro;</td>' .
					                '<td style="width:50px;">' . $importeSeguro . '&euro;</td>' .
					                '<td style="width:120px;"></td>' .
				                    '</tr></table>';
				
				                $total_packages += 1;
				                $total_weight += $peso_paquete;
				                $total_cashondelivery += $cashondelivery;
				                $total_insurance += $importeSeguro;
				                
				                $cr_carriers[$carrier_code]['total_packages'] += 1;
				                $cr_carriers[$carrier_code]['total_weight'] += $peso_paquete;
				                $cr_carriers[$carrier_code]['total_cashondelivery'] += $cashondelivery;
				                $cr_carriers[$carrier_code]['total_insurance'] += $importeSeguro;
                                
                            }
                        }
                	    
                	}
                }
                
                
                /**
                 *  Totales por metodo
                 */
                $cr_carriers_totals = '';
                foreach($cr_carriers as $cr_carrier => $cr_data)
    			{
    				$cr_carriers_totals .= '<table class="total"><tr>' .
    					'<td style="width:200px;">'.$correos_methods[$cr_carrier].'</td>
    						<td style="width:190px;"></td>
    						<td style="width:50px;">'.$cr_data['total_packages'].'</td>
    						<td style="width:50px;">'.number_format((float)$cr_data['total_weight'], 2, '.', '').'</td>
    						<td style="width:50px;">'.number_format((float)$cr_data['total_cashondelivery'], 2, '.', '').'&euro;</td>
    						<td style="width:50px;">'.number_format((float)$cr_data['total_insurance'], 2, '.', '').'&euro;</td>
    						<td style="width:120px;"></td>
    					</tr></table>';
    			}
    			$_totales = array (
    			        'total_packages'        => $total_packages,
    			        'total_weight'          => number_format((float)$total_weight, 2, '.', ''),
    			        'total_cashondelivery'  => number_format((float)$total_cashondelivery, 2, '.', ''),
    			        'total_insurance'       => number_format((float)$total_insurance, 2, '.', ''),
    			        );
    			
    			
    			/**
    			 *  Plantilla base
    			 */
    			$_html = $this->getLayout()
    	                                ->createBlock('core/template')
    	                                ->setData('cliente', Mage::helper('correos')->getValueConfig('nombre', 'remitente') . ' ' . Mage::helper('correos')->getValueConfig('apellidos', 'remitente'))
    	                                ->setData('registros', $records)
    	                                ->setData('carriers', $cr_carriers_totals)
    	                                ->setData('totales', $_totales)
    	                                ->setTemplate('correos/manifiesto.phtml')
    	                                ->toHtml();
    			
    			
    			/**
    			 *  conversion a pdf
    			 */
    			require_once(Mage::getBaseDir('lib') . '/html2pdf/html2pdf.class.php');
    			$path       = Mage::getBaseDir() . DS . 'var' . DS . 'pdf' . DS;
				$html2pdf   = new HTML2PDF('P', 'A4', 'es');
				$html2pdf->writeHTML($_html);
				$html2pdf->Output($path . 'manifiesto.pdf', 'F');
			
    			
                /**
                 *  salida
                 */
                $this->getResponse()
                    ->setHttpResponseCode(200)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Content-type', 'application/pdf', true)
                    //->setHeader('Content-Length', strlen($content))
                    ->setHeader('Content-Disposition', 'attachment; filename="manifiesto.pdf"')
                    ->setHeader('Last-Modified', date('r'));
        
                
                /**
                 *  enviamos el fichero
                 */
                $ioAdapter  = new Varien_Io_File();
                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();
                $ioAdapter->open(array('path' => $ioAdapter->dirname(Mage::getBaseDir() . DS . 'var' . DS . 'pdf' . DS . 'manifiesto.pdf')));
                $ioAdapter->streamOpen(Mage::getBaseDir() . DS . 'var' . DS . 'pdf' . DS . 'manifiesto.pdf', 'r');
                while ($buffer = $ioAdapter->streamRead()) {
                    print $buffer;
                }
                $ioAdapter->streamClose();
        
                exit(0);
    			
                    
                    	    
            } else {
                $this->_getSession()->addError($this->__('No se han encontrado pedidos para imprimir.'));
            }
            
        } catch (Exception $e) {
            
            if (!$e->getMessage())
            {
                //Mage::throwException(Mage::helper('correos')->__($this->__('No se han encontrado pedidos para imprimir.')));
                $this->_getSession()->addError($this->__('No se han encontrado pedidos para imprimir.'));
            } else {
                //Mage::throwException($e->getMessage());
                $this->_getSession()->addError($e->getMessage());
            }
            
        }  
        
            
        $this->_redirectReferer(); 
        return;	    
                	    
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
            } else {
                return true;
            }
        } else {
            
            // Ya existe el fichero
            return true;
            
        }
        
    }
    
    

}