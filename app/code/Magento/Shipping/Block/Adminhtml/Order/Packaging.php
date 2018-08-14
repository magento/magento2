<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Adminhtml\Order;

/**
 * Adminhtml shipment packaging
 *
 * @api
 * @since 100.0.2
 */
class Packaging extends \Magento\Backend\Block\Template
{
    /**
     * Source size model
     *
     * @var \Magento\Shipping\Model\Carrier\Source\GenericInterface
     */
    protected $_sourceSizeModel;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Shipping\Model\Carrier\Source\GenericInterface $sourceSizeModel
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Shipping\Model\Carrier\Source\GenericInterface $sourceSizeModel,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        $this->_sourceSizeModel = $sourceSizeModel;
        $this->_carrierFactory = $carrierFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
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
        $urlParams = [];

        $itemsQty = [];
        $itemsPrice = [];
        $itemsName = [];
        $itemsWeight = [];
        $itemsProductId = [];

        if ($shipmentId) {
            $urlParams['shipment_id'] = $shipmentId;
            $createLabelUrl = $this->getUrl('adminhtml/order_shipment/createLabel', $urlParams);
            $itemsGridUrl = $this->getUrl('adminhtml/order_shipment/getShippingItemsGrid', $urlParams);
            foreach ($this->getShipment()->getAllItems() as $item) {
                $itemsQty[$item->getId()] = $item->getQty();
                $itemsPrice[$item->getId()] = $item->getPrice();
                $itemsName[$item->getId()] = $item->getName();
                $itemsWeight[$item->getId()] = $item->getWeight();
                $itemsProductId[$item->getId()] = $item->getProductId();
                $itemsOrderItemId[$item->getId()] = $item->getOrderItemId();
            }
        } else {
            if ($orderId) {
                $urlParams['order_id'] = $orderId;
                $createLabelUrl = $this->getUrl('adminhtml/order_shipment/save', $urlParams);
                $itemsGridUrl = $this->getUrl('adminhtml/order_shipment/getShippingItemsGrid', $urlParams);

                foreach ($this->getShipment()->getAllItems() as $item) {
                    $itemsQty[$item->getOrderItemId()] = $item->getQty() * 1;
                    $itemsPrice[$item->getOrderItemId()] = $item->getPrice();
                    $itemsName[$item->getOrderItemId()] = $item->getName();
                    $itemsWeight[$item->getOrderItemId()] = $item->getWeight();
                    $itemsProductId[$item->getOrderItemId()] = $item->getProductId();
                    $itemsOrderItemId[$item->getOrderItemId()] = $item->getOrderItemId();
                }
            }
        }
        $data = [
            'createLabelUrl' => $createLabelUrl,
            'itemsGridUrl' => $itemsGridUrl,
            'errorQtyOverLimit' => __(
                'You are trying to add a quantity for some products that doesn\'t match the quantity that was shipped.'
            ),
            'titleDisabledSaveBtn' => __('Products should be added to package(s)'),
            'validationErrorMsg' => __('The value that you entered is not valid.'),
            'shipmentItemsQty' => $itemsQty,
            'shipmentItemsPrice' => $itemsPrice,
            'shipmentItemsName' => $itemsName,
            'shipmentItemsWeight' => $itemsWeight,
            'shipmentItemsProductId' => $itemsProductId,
            'shipmentItemsOrderItemId' => $itemsOrderItemId,
            'customizable' => $this->_getCustomizableContainers(),
        ];
        return $this->_jsonEncoder->encode($data);
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
        $carrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        $countryShipper = $this->_scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($carrier) {
            $params = new \Magento\Framework\DataObject(
                [
                    'method' => $order->getShippingMethod(true)->getMethod(),
                    'country_shipper' => $countryShipper,
                    'country_recipient' => $address->getCountryId(),
                ]
            );
            return $carrier->getContainerTypes($params);
        }
        return [];
    }

    /**
     * Get codes of customizable container types of carrier
     *
     * @return array
     */
    protected function _getCustomizableContainers()
    {
        $order = $this->getShipment()->getOrder();
        $carrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        if ($carrier) {
            return $carrier->getCustomizableContainerTypes();
        }
        return [];
    }

    /**
     * Return name of container type by its code
     *
     * @param string $code
     * @return string
     */
    public function getContainerTypeByCode($code)
    {
        $order = $this->getShipment()->getOrder();
        $carrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
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
        $order = $this->getShipment()->getOrder();
        $carrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        if ($carrier) {
            $params = new \Magento\Framework\DataObject(['country_recipient' => $countryId]);
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
        return $this->getShipment()->getPackages();
    }

    /**
     * Get item of shipment by its id
     *
     * @param string $itemId
     * @param string $itemsOf
     * @return \Magento\Framework\DataObject
     */
    public function getShipmentItem($itemId, $itemsOf)
    {
        $items = $this->getShipment()->getAllItems();
        foreach ($items as $item) {
            if ($itemsOf == 'order' && $item->getOrderItemId() == $itemId) {
                return $item;
            } else {
                if ($itemsOf == 'shipment' && $item->getId() == $itemId) {
                    return $item;
                }
            }
        }
        return new \Magento\Framework\DataObject();
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
        $shipperAddressCountryCode = $this->_scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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
        $order = $this->getShipment()->getOrder();
        $carrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        $params = new \Magento\Framework\DataObject(['country_recipient' => $countryId]);
        if ($carrier && is_array($carrier->getDeliveryConfirmationTypes($params))) {
            return $carrier->getDeliveryConfirmationTypes($params);
        }
        return [];
    }

    /**
     * Print button for creating pdf
     *
     * @return string
     */
    public function getPrintButton()
    {
        $data['shipment_id'] = $this->getShipment()->getId();
        return $this->getUrl('adminhtml/order_shipment/printPackage', $data);
    }

    /**
     * Check whether girth is allowed for current carrier
     *
     * @return bool
     */
    public function isGirthAllowed()
    {
        $order = $this->getShipment()->getOrder();
        $carrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        return $carrier->isGirthAllowed($this->getShipment()->getOrder()->getShippingAddress()->getCountryId());
    }

    /**
     * Is display girth value
     *
     * @return bool
     */
    public function isDisplayGirthValue()
    {
        return false;
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
        $carrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        $countryShipper = $this->_scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($carrier) {
            $params = new \Magento\Framework\DataObject(
                [
                    'method' => $order->getShippingMethod(true)->getMethod(),
                    'country_shipper' => $countryShipper,
                    'country_recipient' => $address->getCountryId(),
                ]
            );
            return $carrier->getContentTypes($params);
        }
        return [];
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
            return $this->getShipment()->getOrder()->getItemById($itemId)->getQtyOrdered() * 1;
        } else {
            return;
        }
    }

    /**
     * Get source size model
     *
     * @return \Magento\Shipping\Model\Carrier\Source\GenericInterface
     */
    public function getSourceSizeModel()
    {
        return $this->_sourceSizeModel;
    }
}
