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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml shipment packaging
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Sales_Order_Shipment_Packaging extends Mage_Adminhtml_Block_Template
{
    /**
     * Retrieve shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        return Mage::registry('current_shipment');
    }

    /**
     * Configuration for popup window for packaging
     *
     * @return string
     */
    public function getConfigDataJson()
    {
        $shipmentId = $this->getShipment()->getId();
        $orderId = $this->getRequest()->getParam('order_id');
        $urlParams = array();

        $itemsQty       = array();
        $itemsPrice     = array();
        $itemsName      = array();
        $itemsWeight    = array();
        $itemsProductId = array();

        if ($shipmentId) {
            $urlParams['shipment_id'] = $shipmentId;
            $createLabelUrl = $this->getUrl('*/sales_order_shipment/createLabel', $urlParams);
            $itemsGridUrl = $this->getUrl('*/sales_order_shipment/getShippingItemsGrid', $urlParams);
            foreach ($this->getShipment()->getAllItems() as $item) {
                $itemsQty[$item->getId()]           = $item->getQty();
                $itemsPrice[$item->getId()]         = $item->getPrice();
                $itemsName[$item->getId()]          = $item->getName();
                $itemsWeight[$item->getId()]        = $item->getWeight();
                $itemsProductId[$item->getId()]     = $item->getProductId();
                $itemsOrderItemId[$item->getId()]   = $item->getOrderItemId();
            }
        } else if ($orderId) {
            $urlParams['order_id'] = $orderId;
            $createLabelUrl = $this->getUrl('*/sales_order_shipment/save', $urlParams);
            $itemsGridUrl = $this->getUrl('*/sales_order_shipment/getShippingItemsGrid', $urlParams);

            foreach ($this->getShipment()->getAllItems() as $item) {
                $itemsQty[$item->getOrderItemId()]          = $item->getQty()*1;
                $itemsPrice[$item->getOrderItemId()]        = $item->getPrice();
                $itemsName[$item->getOrderItemId()]         = $item->getName();
                $itemsWeight[$item->getOrderItemId()]       = $item->getWeight();
                $itemsProductId[$item->getOrderItemId()]    = $item->getProductId();
                $itemsOrderItemId[$item->getOrderItemId()]  = $item->getOrderItemId();
            }
        }
        $data = array(
            'createLabelUrl'            => $createLabelUrl,
            'itemsGridUrl'              => $itemsGridUrl,
            'errorQtyOverLimit'         => Mage::helper('Mage_Sales_Helper_Data')->__('The quantity you want to add exceeds the total shipped quantity for some of selected Product(s)'),
            'titleDisabledSaveBtn'      => Mage::helper('Mage_Sales_Helper_Data')->__('Products should be added to package(s)'),
            'validationErrorMsg'        => Mage::helper('Mage_Sales_Helper_Data')->__('The value that you entered is not valid.'),
            'shipmentItemsQty'          => $itemsQty,
            'shipmentItemsPrice'        => $itemsPrice,
            'shipmentItemsName'         => $itemsName,
            'shipmentItemsWeight'       => $itemsWeight,
            'shipmentItemsProductId'    => $itemsProductId,
            'shipmentItemsOrderItemId'  => $itemsOrderItemId,
            'customizable'              => $this->_getCustomizableContainers(),
        );
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($data);
    }

    /**
     * Return container types of carrier
     *
     * @return array
     */
    public function getContainers()
    {
        $order = $this->getShipment()->getOrder();
        $storeId = $this->getShipment()->getStoreId();
        $address = $order->getShippingAddress();
        $carrier = $order->getShippingCarrier();
        $countryShipper = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $storeId);
        if ($carrier) {
            $params = new Varien_Object(array(
                'method' => $order->getShippingMethod(true)->getMethod(),
                'country_shipper' => $countryShipper,
                'country_recipient' => $address->getCountryId(),
            ));
            return $carrier->getContainerTypes($params);
        }
        return array();
    }

    /**
     * Get codes of customizable container types of carrier
     *
     * @return array
     */
    protected function _getCustomizableContainers()
    {
        $carrier = $this->getShipment()->getOrder()->getShippingCarrier();
        if ($carrier) {
            return $carrier->getCustomizableContainerTypes();
        }
        return array();
    }

    /**
     * Return name of container type by its code
     *
     * @param string $code
     * @return string
     */
    public function getContainerTypeByCode($code)
    {
        $carrier = $this->getShipment()->getOrder()->getShippingCarrier();
        if ($carrier) {
            $containerTypes = $carrier->getContainerTypes();
            $containerType = !empty($containerTypes[$code]) ? $containerTypes[$code] : '';
            return $containerType;
        }
        return '';
    }

    /**
     * Return name of delivery confirmation type by its code
     *
     * @param string $code
     * @return string
     */
    public function getDeliveryConfirmationTypeByCode($code)
    {
        $countryId = $this->getShipment()->getOrder()->getShippingAddress()->getCountryId();
        $carrier = $this->getShipment()->getOrder()->getShippingCarrier();
        if ($carrier) {
            $params = new Varien_Object(array('country_recipient' => $countryId));
            $confirmationTypes = $carrier->getDeliveryConfirmationTypes($params);
            $confirmationType = !empty($confirmationTypes[$code]) ? $confirmationTypes[$code] : '';
            return $confirmationType;
        }
        return '';
    }

    /**
     * Return name of content type by its code
     *
     * @param string $code
     * @return string
     */
    public function getContentTypeByCode($code)
    {
        $contentTypes = $this->getContentTypes();
        if (!empty($contentTypes[$code])) {
            return $contentTypes[$code];
        }
        return '';
    }

    /**
     * Get packed products in packages
     *
     * @return array
     */
    public function getPackages()
    {
        $packages = $this->getShipment()->getPackages();
        if ($packages) {
            $packages = unserialize($packages);
        } else {
            $packages = array();
        }
        return $packages;
    }

    /**
     * Get item of shipment by its id
     *
     * @param  $itemId
     * @param  $itemsOf
     * @return Varien_Object
     */
    public function getShipmentItem($itemId, $itemsOf)
    {
        $items = $this->getShipment()->getAllItems();
        foreach ($items as $item) {
            if ($itemsOf == 'order' && $item->getOrderItemId() == $itemId) {
                return $item;
            } else if ($itemsOf == 'shipment' && $item->getId() == $itemId) {
                return $item;
            }
        }
        return new Varien_Object();
    }

    /**
     * Can display customs value
     *
     * @return bool
     */
    public function displayCustomsValue()
    {
        $storeId = $this->getShipment()->getStoreId();
        $order = $this->getShipment()->getOrder();
        $address = $order->getShippingAddress();
        $shipperAddressCountryCode = Mage::getStoreConfig(
            Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
            $storeId
        );
        $recipientAddressCountryCode = $address->getCountryId();
        if ($shipperAddressCountryCode != $recipientAddressCountryCode) {
            return true;
        }
        return false;
    }

    /**
     * Return delivery confirmation types of current carrier
     *
     * @return array
     */
    public function getDeliveryConfirmationTypes()
    {
        $countryId = $this->getShipment()->getOrder()->getShippingAddress()->getCountryId();
        $carrier = $this->getShipment()->getOrder()->getShippingCarrier();
        $params = new Varien_Object(array('country_recipient' => $countryId));
        if ($carrier && is_array($carrier->getDeliveryConfirmationTypes($params))) {
            return $carrier->getDeliveryConfirmationTypes($params);
        }
        return array();
    }

    /**
     * Print button for creating pdf
     *
     * @return string
     */
    public function getPrintButton()
    {
        $data['shipment_id'] = $this->getShipment()->getId();
        $url = $this->getUrl('*/sales_order_shipment/printPackage', $data);
        return $this->getLayout()
            ->createBlock('Mage_Adminhtml_Block_Widget_Button')
            ->setData(array(
                'label'   => Mage::helper('Mage_Sales_Helper_Data')->__('Print'),
                'onclick' => 'setLocation(\'' . $url . '\')'
            ))
            ->toHtml();
    }

    /**
     * Check whether girth is allowed for current carrier
     *
     * @return void
     */
    public function isGirthAllowed()
    {
        return $this
            ->getShipment()
            ->getOrder()
            ->getShippingCarrier()
            ->isGirthAllowed($this->getShipment()->getOrder()->getShippingAddress()->getCountryId());
    }

    /**
     * Return content types of package
     *
     * @return array
     */
    public function getContentTypes()
    {
        $order = $this->getShipment()->getOrder();
        $storeId = $this->getShipment()->getStoreId();
        $address = $order->getShippingAddress();
        $carrier = $order->getShippingCarrier();
        $countryShipper = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $storeId);
        if ($carrier) {
            $params = new Varien_Object(array(
                'method' => $order->getShippingMethod(true)->getMethod(),
                'country_shipper' => $countryShipper,
                'country_recipient' => $address->getCountryId(),
            ));
            return $carrier->getContentTypes($params);
        }
        return array();
    }

    /**
     * Get Currency Code for Custom Value
     *
     * @return string
     */
    public function getCustomValueCurrencyCode()
    {
        $orderInfo = $this->getShipment()->getOrder();
        return $orderInfo->getBaseCurrency()->getCurrencyCode();
    }

    /**
     * Display formatted price
     *
     * @param float $price
     * @return string
     */
    public function displayPrice($price)
    {
        return $this->getShipment()->getOrder()->formatPriceTxt($price);
    }

    /**
     * Display formatted customs price
     *
     * @param float $price
     * @return string
     */
    public function displayCustomsPrice($price)
    {
        $orderInfo = $this->getShipment()->getOrder();
        return $orderInfo->getBaseCurrency()->formatTxt($price);
    }

    /**
     * Get ordered qty of item
     *
     * @param int $itemId
     * @return int|null
     */
    public function getQtyOrderedItem($itemId)
    {
        if ($itemId) {
            return $this->getShipment()->getOrder()->getItemById($itemId)->getQtyOrdered()*1;
        } else {
            return;
        }
    }
}
