<?php

/** 
 *
 */
class Ydral_Correos_Adminhtml_MailController extends Mage_Adminhtml_Controller_Action 
{

    /**
     * Export shipping table rates in csv format
     *
     */
    public function recogidaAction()
    {
        
        try
        {
            $orderIds = $this->getRequest()->getPost('order_ids');

            if (!empty($orderIds))
            {
                $this->loadLayout();
                $this->renderLayout();
            } else {
                $this->_getSession()->addError($this->__('No se han encontrado pedidos para solicitar recogida.'));
            }
            
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('correos')->__('No se han encontrado pedidos para solicitar recogida.')
            );
            return false;
        }  
        
        return;
        
    }
    
    
    
    public function saveAction()
    {
        try
        {
        
            $orderIds = $this->getRequest()->getPost('orderids');
            $bultos = $this->getRequest()->getPost('bultos');
            $horario = $this->getRequest()->getPost('horario');
            $recogida = $this->getRequest()->getPost('recogida');
            $observaciones = $this->getRequest()->getPost('observaciones');
            
            
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
            
            
            if (empty($bultos)) { Mage::throwException(Mage::helper('correos')->__('El campo "Bultos" es obligatorio.')); }
            if (empty($horario)) { Mage::throwException(Mage::helper('correos')->__('El campo "Horario de recogida" es obligatorio.')); }
            if (empty($recogida)) { Mage::throwException(Mage::helper('correos')->__('El campo "Fecha de recogida" es obligatorio.')); }
            
            if (!empty($orderIds))
            {
                
                $_dataMail = array(
                    'etiquetador'   => Mage::helper('correos')->getValueConfig('numcliente', 'general', $_order->getStoreId()),
                    'contrato'      => Mage::helper('correos')->getValueConfig('contrato', 'general', $_order->getStoreId()),
                    'telefono'      => $this->getRequest()->getPost('telefono'),
                    'empresa'      => $this->getRequest()->getPost('empresa'),
                    'nombre'      => $this->getRequest()->getPost('nombre'),
                    'apellidos'      => $this->getRequest()->getPost('apellidos'),
                    'direccion'      => $this->getRequest()->getPost('direccion'),
                    'localidad'      => $this->getRequest()->getPost('localidad'),
                    'cp'      => $this->getRequest()->getPost('cp'),
                    'provincia'      => $this->getRequest()->getPost('provincia'),
                    'movil'      => $this->getRequest()->getPost('movil'),
                );
                
                $contentMail = "
                <h2>
                    N&ordm; CLIENTE: {$_dataMail['etiquetador']}
                    <br />
                    N&ordm; CONTRATO: {$_dataMail['contrato']}
                </h2>
                
                <br />**************************************************************************<br /><br />
                
                <h3>Datos de Recogida</h3>
                <br />
                Tel&eacute;fono: {$_dataMail['telefono']}<br/>
				Nombre: {$_dataMail['nombre']}<br/>
				Apellidos: {$_dataMail['apellidos']}<br/>
				Calle: {$_dataMail['direccion']}<br/>
				CP: {$_dataMail['cp']}<br/>
				Poblaci&oacute;n: {$_dataMail['localidad']}<br/>
				Provincia: {$_dataMail['provincia']}<br/>
				<br/><br />
				<strong>N&uacute;mero de Bulto(s)</strong>: {$bultos}
				<br/><br />
				Servicio: Paq72
				<br />
				Fecha de recogida: {$recogida}
				<br />
				Horario: {$horario}
				<br /><br />
				<strong>Observaciones:</strong> {$observaciones}
				<br /><br />
				<h3>C&oacute;digo(s) de env&iacute;o</h3>
                ";
                
                $order_ids = explode(',', $orderIds);
                foreach ($order_ids as $order_id)
                {
                    $_order = Mage::getModel('sales/order')->load($order_id);        
                    $tracks = $_order->getTracksCollection();
                    $tracksValidos = array ('Correos', 'envio48', 'envio72', 'recogeroficina48', 'recogeroficina72', 'correosinter', 'homepaq48', 'homepaq72');
                    foreach ($tracks as $track)
                    {
                        if (in_array($track->getCarrierCode(), $tracksValidos))
                        {
                            $contentMail .= $track['number'] . "<br />";
                        }
                    }
                }
                
                
                /**
                 *  send email
                 */
                $mail = Mage::getModel('core/email');
                $mail->setToName('Correos Recogida');
                $mail->setToEmail('buzonrecogidasesporadicas@correos.com');
                $mail->setBody($contentMail);
                $mail->setSubject('Solicitud de recogida.');
                $mail->setFromEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
                $mail->setFromName(Mage::getStoreConfig('trans_email/ident_general/name'));
                $mail->setType('html');
                
                $mail->send();
                
                Mage::getSingleton('core/session')->addSuccess('Your request has been sent');
   
                
            } else {
                $this->_getSession()->addError($this->__('No se han encontrado pedidos para solicitar recogida.'));
            }
            
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('correos')->__('No se han encontrado pedidos para imprimir.')
            );
            return false;
        }  
        
            
        $this->_redirect("adminhtml/sales_order/index");
        return;
        
    }
    
    
}