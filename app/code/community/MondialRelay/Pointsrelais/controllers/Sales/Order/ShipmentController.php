<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order shipment controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';
class MondialRelay_Pointsrelais_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     */
    
	public function getConfigData($field)
	{
        $path = 'carriers/pointsrelais/'.$field;
        return Mage::getStoreConfig($path, Mage::app()->getStore());
	}
    
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');

        try {
            
            if ($shipment = $this->_initShipment()) {
                
                //Si l'expedition est réalisé par Mondial Relay, on créé le tracking automatiquement.
                
                $_order = $shipment->getOrder();
                $_shippingMethod = explode("_",$_order->getShippingMethod());
                
                if ($_shippingMethod[0] == 'pointsrelais')  {
                    
                    // On met en place les paramètres de la requète
                    
                    $adress = $_order->getShippingAddress()->getStreet();
                    if (!isset($adress[1]))
                    {
                        $adress[1] = '';
                    }
                    $params = array(
                                   'Enseigne'       => $this->getConfigData('enseigne'),
                                   'ModeCol'        => 'CCC',
                                   'ModeLiv'        => '24R',
                                   'Expe_Langage'   => 'FR',
                                   'Expe_Ad1'       => $this->getConfigData('adresse1_enseigne'),
                                   'Expe_Ad3'       => $this->getConfigData('adresse3_enseigne'),
                                   'Expe_Ad4'       => $this->getConfigData('adresse4_enseigne'),
                                   'Expe_Ville'     => $this->getConfigData('ville_enseigne'),
                                   'Expe_CP'        => $this->getConfigData('cp_enseigne'),
                                   'Expe_Pays'      => $this->getConfigData('pays_enseigne'),
                                   'Expe_Tel1'      => $this->getConfigData('tel_enseigne'),
                                   'Expe_Tel2'      => $this->getConfigData('mobile_enseigne'),
                                   'Expe_Mail'      => $this->getConfigData('mail_enseigne'),
                                   'Dest_Langage'   => 'FR',
                                   'Dest_Ad1'       => $_order->getShippingAddress()->getFirstname() . ' ' . $_order->getShippingAddress()->getLastname(),
                                   'Dest_Ad2'       => $_order->getShippingAddress()->getCompagny(),
                                   'Dest_Ad3'       => $adress[0],
                                   'Dest_Ad4'       => $adress[1],                                   
                                   'Dest_Ville'     => $_order->getShippingAddress()->getCity(),
                                   'Dest_CP'        => $_order->getShippingAddress()->getPostcode(),
                                   'Dest_Pays'      => $_order->getShippingAddress()->getCountryId(),
                                   'Dest_Tel1'      => $_order->getShippingAddress()->getTelephone(),
                                   'Dest_Mail'      => $_order->getCustomerEmail(),
                                   'Poids'          => $_order->getWeight()*1000,
                                   'NbColis'        => '1',
                                   'CRT_Valeur'     => '0',
                                   'LIV_Rel_Pays'   => $_order->getShippingAddress()->getCountryId(),
                                   'LIV_Rel'        => $_shippingMethod[1]
                    );
                    //On crée le code de sécurité
                    $code = implode("",$params);
                    $code .= $this->getConfigData('cle');
                    
                    //On le rajoute aux paramètres
                    $params["Security"] = strtoupper(md5($code));
                    
                    // On se connecte
                    $client = new SoapClient("http://www.mondialrelay.fr/WebService/Web_Services.asmx?WSDL");
                    
                    // Et on effectue la requète
                    $expedition = $client->WSI2_CreationExpedition($params)->WSI2_CreationExpeditionResult;
                    
                    $track = Mage::getModel('sales/order_shipment_track')
                        ->setNumber($expedition->ExpeditionNum)
                        ->setCarrier('Mondial Relay')
                        ->setCarrierCode($_shippingMethod[0])
                        ->setTitle('Mondial Relay')
                        ->setPopup(1));
                    $shipment->addTrack($track);
                }
                $shipment->register();
                $comment = '';
                if (!empty($data['comment_text'])) {
                    $shipment->addComment($data['comment_text'], isset($data['comment_customer_notify']));
                    $comment = $data['comment_text'];
                }

                if (!empty($data['send_email'])) {
                    $shipment->setEmailSent(true);
                }

                $this->_saveShipment($shipment);
                $shipment->sendEmail(!empty($data['send_email']), $comment);
                $this->_getSession()->addSuccess($this->__('Shipment was successfully created.'));
                $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
                return;
            }
            else {
                $this->_forward('noRoute');
                return;
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Can not save shipment.'));
        }
        $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
    }
    
}
