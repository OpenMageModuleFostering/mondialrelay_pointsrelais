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
 * Adminhtml shipment create
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MondialRelay_Pointsrelais_Block_Sales_Order_Shipment_View extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId    = 'shipment_id';
        $this->_controller  = 'sales_order_shipment';
        $this->_mode        = 'view';

        parent::__construct();

        $this->_removeButton('reset');
        $this->_removeButton('delete');
        $this->_updateButton('save', 'label', Mage::helper('sales')->__('Send Tracking Information'));
        $this->_updateButton('save', 'onclick', "setLocation('".$this->getEmailUrl()."')");
        
        //Ajout de l'impression de l'étiquette
        $_order = $this->getShipment()->getOrder();
        $_shippingMethod = explode("_",$_order->getShippingMethod());
        if (($_shippingMethod[0] == 'pointsrelais') || ($_shippingMethod[0] == 'pointsrelaisld1') || ($_shippingMethod[0] == 'pointsrelaislds'))  {
            $this->_addButton('etiquette', array(
                'label'     => Mage::helper('pointsrelais')->__('Etiquette Mondial Relay'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\'' . $this->getPrintMondialRelayUrl() . '\')'
                )
            );
        }
        
        if ($this->getShipment()->getId()) {
            $this->_addButton('print', array(
                'label'     => Mage::helper('sales')->__('Print'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getPrintUrl().'\')'
                )
            );
        }
    }

    /**
     * Retrieve shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        return Mage::registry('current_shipment');
    }

    public function getHeaderText()
    {
        if ($this->getShipment()->getEmailSent()) {
            $emailSent = Mage::helper('sales')->__('Shipment email sent');
        }
        else {
            $emailSent = Mage::helper('sales')->__('Shipment email not sent');
        }

        $header = Mage::helper('sales')->__('Shipment #%s (%s)', $this->getShipment()->getIncrementId(), $emailSent);
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl(
            '*/sales_order/view',
            array(
                'order_id'  => $this->getShipment()->getOrderId(),
                'active_tab'=> 'order_shipments'
            ));
    }

    public function getEmailUrl()
    {
        return $this->getUrl('*/sales_order_shipment/email', array('shipment_id'  => $this->getShipment()->getId()));
    }

    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print', array(
            'invoice_id' => $this->getShipment()->getId()
        ));
    }
    
    public function getPrintMondialRelayUrl()
    {
        return $this->getUrl('pointsrelais/sales_impression/print', array(
            'shipment_id' => $this->getShipment()->getId()
        ));
    }
    
    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/sales_shipment/') . '\')');
        }
        return $this;
    }
}