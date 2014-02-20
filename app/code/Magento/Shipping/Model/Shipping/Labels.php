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
 * @category    Magento
 * @package     Magento_Shipping
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Shipping\Model\Shipping;

use \Magento\Sales\Model\Order\Shipment;

/**
 * Shipping labels model
 */
class Labels extends \Magento\Shipping\Model\Shipping
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Shipping\Model\Shipment\Request
     */
    protected $_request;

    /**
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Math\Division $mathDivision
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Shipping\Model\Shipment\Request $request
     */
    public function __construct(
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Math\Division $mathDivision,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Shipping\Model\Shipment\Request $request
    ) {
        $this->_authSession = $authSession;
        $this->_request = $request;
        parent::__construct(
            $coreStoreConfig,
            $shippingConfig,
            $storeManager,
            $carrierFactory,
            $rateResultFactory,
            $shipmentRequestFactory,
            $regionFactory,
            $mathDivision
        );
    }

    /**
     * Prepare and do request to shipment
     *
     * @param Shipment $orderShipment
     * @return \Magento\Object
     * @throws \Magento\Core\Exception
     */
    public function requestToShipment(Shipment $orderShipment)
    {
        $admin = $this->_authSession->getUser();
        $order = $orderShipment->getOrder();
        $address = $order->getShippingAddress();
        $shippingMethod = $order->getShippingMethod(true);
        $shipmentStoreId = $orderShipment->getStoreId();
        $shipmentCarrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        $baseCurrencyCode = $this->_storeManager->getStore($shipmentStoreId)->getBaseCurrencyCode();
        if (!$shipmentCarrier) {
            throw new \Magento\Core\Exception('Invalid carrier: ' . $shippingMethod->getCarrierCode());
        }
        $shipperRegionCode = $this->_coreStoreConfig->getConfig(Shipment::XML_PATH_STORE_REGION_ID, $shipmentStoreId);
        if (is_numeric($shipperRegionCode)) {
            $shipperRegionCode = $this->_regionFactory->create()->load($shipperRegionCode)->getCode();
        }

        $recipientRegionCode = $this->_regionFactory->create()->load($address->getRegionId())->getCode();

        $originStreet1 = $this->_coreStoreConfig->getConfig(Shipment::XML_PATH_STORE_ADDRESS1, $shipmentStoreId);
        $originStreet2 = $this->_coreStoreConfig->getConfig(Shipment::XML_PATH_STORE_ADDRESS2, $shipmentStoreId);
        $storeInfo = new \Magento\Object(
            (array)$this->_coreStoreConfig->getConfig('general/store_information', $shipmentStoreId)
        );

        if (!$admin->getFirstname() || !$admin->getLastname() || !$storeInfo->getName() || !$storeInfo->getPhone()
            || !$originStreet1 || !$shipperRegionCode
            || !$this->_coreStoreConfig->getConfig(Shipment::XML_PATH_STORE_CITY, $shipmentStoreId)
            || !$this->_coreStoreConfig->getConfig(Shipment::XML_PATH_STORE_ZIP, $shipmentStoreId)
            || !$this->_coreStoreConfig->getConfig(Shipment::XML_PATH_STORE_COUNTRY_ID, $shipmentStoreId)
        ) {
            throw new \Magento\Core\Exception(
                __('We don\'t have enough information to create shipping labels. Please make sure your store information and settings are complete.')
            );
        }

        /** @var $request \Magento\Shipping\Model\Shipment\Request */
        $request = $this->_shipmentRequestFactory->create();
        $request->setOrderShipment($orderShipment);
        $request->setShipperContactPersonName($admin->getName());
        $request->setShipperContactPersonFirstName($admin->getFirstname());
        $request->setShipperContactPersonLastName($admin->getLastname());
        $request->setShipperContactCompanyName($storeInfo->getName());
        $request->setShipperContactPhoneNumber($storeInfo->getPhone());
        $request->setShipperEmail($admin->getEmail());
        $request->setShipperAddressStreet(trim($originStreet1 . ' ' . $originStreet2));
        $request->setShipperAddressStreet1($originStreet1);
        $request->setShipperAddressStreet2($originStreet2);
        $request->setShipperAddressCity(
            $this->_coreStoreConfig->getConfig(Shipment::XML_PATH_STORE_CITY, $shipmentStoreId)
        );
        $request->setShipperAddressStateOrProvinceCode($shipperRegionCode);
        $request->setShipperAddressPostalCode(
            $this->_coreStoreConfig->getConfig(Shipment::XML_PATH_STORE_ZIP, $shipmentStoreId)
        );
        $request->setShipperAddressCountryCode(
            $this->_coreStoreConfig->getConfig(Shipment::XML_PATH_STORE_COUNTRY_ID, $shipmentStoreId)
        );
        $request->setRecipientContactPersonName(trim($address->getFirstname() . ' ' . $address->getLastname()));
        $request->setRecipientContactPersonFirstName($address->getFirstname());
        $request->setRecipientContactPersonLastName($address->getLastname());
        $request->setRecipientContactCompanyName($address->getCompany());
        $request->setRecipientContactPhoneNumber($address->getTelephone());
        $request->setRecipientEmail($address->getEmail());
        $request->setRecipientAddressStreet(trim($address->getStreet1() . ' ' . $address->getStreet2()));
        $request->setRecipientAddressStreet1($address->getStreet1());
        $request->setRecipientAddressStreet2($address->getStreet2());
        $request->setRecipientAddressCity($address->getCity());
        $request->setRecipientAddressStateOrProvinceCode($address->getRegionCode());
        $request->setRecipientAddressRegionCode($recipientRegionCode);
        $request->setRecipientAddressPostalCode($address->getPostcode());
        $request->setRecipientAddressCountryCode($address->getCountryId());
        $request->setShippingMethod($shippingMethod->getMethod());
        $request->setPackageWeight($order->getWeight());
        $request->setPackages($orderShipment->getPackages());
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($shipmentStoreId);

        return $shipmentCarrier->requestToShipment($request);
    }
}
