<?php

class MondialRelay_Pointsrelais_Sales_ImpressionController extends Mage_Adminhtml_Controller_Action
{
    protected $_trackingNumbers = '';
    
    /**
     * Additional initialization
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('MondialRelay_Pointsrelais');
    }


    /**
     * Shipping grid
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/pointsrelais')
            ->_addContent($this->getLayout()->createBlock('pointsrelais/sales_impression'))
            ->renderLayout();
    }
    
	public function getConfigData($field)
	{
        $path = 'carriers/pointsrelais/'.$field;
        return Mage::getStoreConfig($path, Mage::app()->getStore());
	}
    
    protected function _processDownload($resource, $resourceType)
    {
        $helper = Mage::helper('downloadable/download');
        /* @var $helper Mage_Downloadable_Helper_Download */

        $helper->setResource($resource, $resourceType);

        $fileName       = $helper->getFilename();
        $contentType    = $helper->getContentType();

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true);

        if ($fileSize = $helper->getFilesize()) {
            $this->getResponse()
                ->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()
                ->setHeader('Content-Disposition', $contentDisposition . '; filename='.$fileName);
        }

        $this->getResponse()
            ->clearBody();
        $this->getResponse()
            ->sendHeaders();

        $helper->output();
    }
    
    protected function getTrackingNumber($shipmentId)
    {
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        
        //On récupère le numéro de tracking
        $tracks = $shipment->getTracksCollection();
        foreach ($tracks as $track) {
            if ( $track->getParentId() == $shipmentId)
            {
                $this->_trackingNumbers .= $track->getnumber();
            }
        }
        
        return $this->_trackingNumbers;
    }
    
    protected function getEtiquetteUrl($shipmentsIds)
    {
        //On récupère les infos d'expédition
        if (is_array($shipmentsIds))
        {
            for ($i = 0; $i < count($shipmentsIds); $i++)
            {
                $this->getTrackingNumber($shipmentsIds[$i]);
                if ( $i < count($shipmentsIds)-1)
                {
                    $this->_trackingNumbers .= ';';
                }
            }
        }
        else
        {
            $shipmentId = $shipmentsIds;
            $this->getTrackingNumber($shipmentId);            
        };
        
        // On met en place les paramètres de la requète
        $params = array(
                       'Enseigne'       => $this->getConfigData('enseigne'),
                       'Expeditions'    => $this->_trackingNumbers,
                       'Langue'    => 'FR',
        );
        //On crée le code de sécurité
        $code = implode("",$params);
        $code .= $this->getConfigData('cle');
        
        //On le rajoute aux paramètres
        $params["Security"] = strtoupper(md5($code));
        
        // On se connecte
        $client = new SoapClient("http://www.mondialrelay.fr/WebService/Web_Services.asmx?WSDL");
        
        // Et on effectue la requète
        $etiquette = $client->WSI2_GetEtiquettes($params)->WSI2_GetEtiquettesResult;
        
        return $etiquette->URL_PDF_A5;
    }
    
    public function printMassAction()
    {
        $shipmentsIds = $this->getRequest()->getPost('shipment_ids');
        
        try {
            $urlEtiquette = $this->getEtiquetteUrl($shipmentsIds);
            $this->_processDownload('http://www.mondialrelay.fr' . $urlEtiquette, 'url');
            exit(0);
        } catch (Mage_Core_Exception $e) {
            $this->getSession()->addError(Mage::helper('pointsrelais')->__('Désolé, une erreure est survenu lors de la récupération de l\'étiquetes. Merci de contacter Mondial Relay ou de réessayer plus tard'));
        }
        return $this->_redirectReferer();
        
    }

    public function printAction()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        
        try {
            $urlEtiquette = $this->getEtiquetteUrl($shipmentId);
            $this->_processDownload('http://www.mondialrelay.fr' . $urlEtiquette, 'url');
            exit(0);
        } catch (Mage_Core_Exception $e) {
            $this->getSession()->addError(Mage::helper('pointsrelais')->__('Désolé, une erreure est survenu lors de la récupération de l\'étiquetes. Merci de contacter Mondial Relay ou de réessayer plus tard'));
        }
        return $this->_redirectReferer();
    }
    
}