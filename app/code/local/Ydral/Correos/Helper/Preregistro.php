<?php 

/**
 *
 */
class Ydral_Correos_Helper_Preregistro extends Mage_Core_Helper_Abstract
{

    /**
     * @param array $data
     * @return string
     */
    public function getXmlPreregistro(array $data)
    {
     
        if (empty($data)) return false;
        
        $xmlSend = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns="http://www.correos.es/iris6/services/preregistroetiquetas">
<soapenv:Header/>
<soapenv:Body>
<PreregistroEnvio>
    <fechaOperacion>{$data['fechaOperacion']}</fechaOperacion>
    {$data['dataAuth']}
    <Care>000000</Care>
    <TotalBultos>1</TotalBultos>
    <ModDevEtiqueta>2</ModDevEtiqueta>
    <Remitente>
        <Identificacion>
            <Nombre><![CDATA[{$data['datosRemitente']['Nombre']}]]></Nombre>
            <Apellido1><![CDATA[{$data['datosRemitente']['Apellidos']}]]></Apellido1>
            <Nif><![CDATA[{$data['datosRemitente']['Nif']}]]></Nif>
            <Empresa><![CDATA[{$data['datosRemitente']['Empresa']}]]></Empresa>
            <PersonaContacto><![CDATA[{$data['datosRemitente']['Personacontacto']}]]></PersonaContacto>
        </Identificacion>
        <DatosDireccion>
            <Direccion><![CDATA[{$data['datosRemitente']['Direccion']}]]></Direccion>
            <Localidad><![CDATA[{$data['datosRemitente']['Localidad']}]]></Localidad>
            <Provincia><![CDATA[{$data['datosRemitente']['Provincia']}]]></Provincia>
        </DatosDireccion>
        {$data['datosRemitente']['CP']}
        <Telefonocontacto><![CDATA[{$data['datosRemitente']['Telefono']}]]></Telefonocontacto>
        <Email><![CDATA[{$data['datosRemitente']['Email']}]]></Email>
        <DatosSMS>
            <NumeroSMS><![CDATA[{$data['datosRemitente']['Sms']}]]></NumeroSMS>
            <Idioma>1</Idioma>
        </DatosSMS>
    </Remitente> 
    <Destinatario> 
        <Identificacion> 
            <Nombre><![CDATA[{$data['DatosDestinatario']['Nombre']}]]></Nombre>
            <Apellido1><![CDATA[{$data['DatosDestinatario']['Apellidos']}]]></Apellido1>
            {$data['DatosDestinatario']['Empresa']}
            {$data['DatosDestinatario']['PersonaContacto']}
        </Identificacion>
        <DatosDireccion>
            <Direccion><![CDATA[{$data['DatosDestinatario']['Direccion']}]]></Direccion>
            <Localidad><![CDATA[{$data['DatosDestinatario']['Localidad']}]]></Localidad>
            <Provincia><![CDATA[{$data['DatosDestinatario']['Provincia']}]]></Provincia>
        </DatosDireccion>
        {$data['DatosDestinatario']['CP']}
        <ZIP><![CDATA[{$data['DatosDestinatario']['ZIP']}]]></ZIP>
        <Pais><![CDATA[{$data['DatosDestinatario']['Pais']}]]></Pais>
        <Telefonocontacto><![CDATA[{$data['DatosDestinatario']['Telefono']}]]></Telefonocontacto>
        <Email><![CDATA[{$data['DatosDestinatario']['Email']}]]></Email>
        <DatosSMS>
            <NumeroSMS><![CDATA[{$data['DatosDestinatario']['Sms']}]]></NumeroSMS>
            <Idioma><![CDATA[{$data['DatosDestinatario']['Idioma']}]]></Idioma>
        </DatosSMS>
    </Destinatario> 
    <Envio>
        <CodProducto>{$data['DatosEnvio']['CodProducto']}</CodProducto>
        <ReferenciaCliente>{$data['DatosEnvio']['ReferenciaCliente']}</ReferenciaCliente>
        <TipoFranqueo>{$data['DatosEnvio']['TipoFranqueo']}</TipoFranqueo>
        <ModalidadEntrega>{$data['DatosEnvio']['ModalidadEntrega']}</ModalidadEntrega>
        {$data['DatosEnvio']['OficinaElegida']}
        <Pesos>
            <Peso>
                <TipoPeso>{$data['DatosEnvio']['Peso_Tipo']}</TipoPeso>
                <Valor>{$data['DatosEnvio']['Peso_Valor']}</Valor>
            </Peso>
        </Pesos>
        {$data['DatosEnvio']['Largo']}
        {$data['DatosEnvio']['Alto']}
        {$data['DatosEnvio']['Ancho']}
        <ValoresAnadidos>
            {$data['DatosEnvio']['ValoresAnadidos']['ImporteSeguro']}
            {$data['DatosEnvio']['ValoresAnadidos']['Reembolso']}
            {$data['DatosEnvio']['ValoresAnadidos']['EntregaExclusivaDestinatario']}
            {$data['DatosEnvio']['ValoresAnadidos']['FranjaHorariaConcertada']}
         </ValoresAnadidos>
         <Observaciones1><![CDATA[{$data['DatosDestinatario']['DireccionAlt']}]]></Observaciones1>
         <Observaciones2><![CDATA[{$data['observaciones']}]]></Observaciones2>
        {$data['DatosEnvio']['InstruccionesDevolucion']}
        {$data['DatosEnvio']['Aduana']['tipoAduana']}
        {$data['DatosEnvio']['Homepaq']['tipoHomepaq']}
    </Envio>
</PreregistroEnvio>
</soapenv:Body>
</soapenv:Envelope>
XML;
        
        return $xmlSend;
        
    }

}