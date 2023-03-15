<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model\Shipping;

use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Division as MathDivision;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Address;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Shipping\Model\Shipment\RequestFactory;
use Magento\Shipping\Model\Shipping;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\User;

/**
 * Shipping labels model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Labels extends Shipping
{
    /**
     * @var AuthSession
     */
    protected $_authSession;

    /**
     * @var Request
     */
    protected $_request;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ShippingConfig $shippingConfig
     * @param StoreManagerInterface $storeManager
     * @param CarrierFactory $carrierFactory
     * @param ResultFactory $rateResultFactory
     * @param RequestFactory $shipmentRequestFactory
     * @param RegionFactory $regionFactory
     * @param MathDivision $mathDivision
     * @param StockRegistryInterface $stockRegistry
     * @param AuthSession $authSession
     * @param Request $request
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ShippingConfig $shippingConfig,
        StoreManagerInterface $storeManager,
        CarrierFactory $carrierFactory,
        ResultFactory $rateResultFactory,
        RequestFactory $shipmentRequestFactory,
        RegionFactory $regionFactory,
        MathDivision $mathDivision,
        StockRegistryInterface $stockRegistry,
        AuthSession $authSession,
        Request $request
    ) {
        $this->_authSession = $authSession;
        $this->_request = $request;
        parent::__construct(
            $scopeConfig,
            $shippingConfig,
            $storeManager,
            $carrierFactory,
            $rateResultFactory,
            $shipmentRequestFactory,
            $regionFactory,
            $mathDivision,
            $stockRegistry
        );
    }

    /**
     * Prepare and do request to shipment
     *
     * @param Shipment $orderShipment
     * @return DataObject
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function requestToShipment(Shipment $orderShipment)
    {
        $admin = $this->_authSession->getUser();
        $order = $orderShipment->getOrder();

        $shippingMethod = $order->getShippingMethod(true);
        $shipmentStoreId = $orderShipment->getStoreId();
        $shipmentCarrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        $baseCurrencyCode = $this->_storeManager->getStore($shipmentStoreId)->getBaseCurrencyCode();
        if (!$shipmentCarrier) {
            throw new LocalizedException(
                __('The "%1" carrier is invalid. Verify and try again.', $shippingMethod->getCarrierCode())
            );
        }
        $shipperRegionCode = $this->_scopeConfig->getValue(
            Shipment::XML_PATH_STORE_REGION_ID,
            ScopeInterface::SCOPE_STORE,
            $shipmentStoreId
        );
        if (is_numeric($shipperRegionCode)) {
            $shipperRegionCode = $this->_regionFactory->create()->load($shipperRegionCode)->getCode();
        }

        $originStreet1 = $this->_scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS1,
            ScopeInterface::SCOPE_STORE,
            $shipmentStoreId
        );
        $storeInfo = new DataObject(
            (array)$this->_scopeConfig->getValue(
                'general/store_information',
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
        );

        if (!$admin->getFirstName()
            || !$admin->getLastName()
            || !$storeInfo->getName()
            || !$storeInfo->getPhone()
            || !$originStreet1
            || !$this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
            || !$this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
            || !$this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
        ) {
            throw new LocalizedException(
                __(
                    "Shipping labels can't be created. "
                    . "Verify that the store information and settings are complete and try again."
                )
            );
        }

        /** @var Request $request */
        $request = $this->_shipmentRequestFactory->create();
        $request->setOrderShipment($orderShipment);
        $address = $order->getShippingAddress();

        $this->setShipperDetails($request, $admin, $storeInfo, $shipmentStoreId, $shipperRegionCode, $originStreet1);
        $this->setRecipientDetails($request, $address);

        $request->setShippingMethod($shippingMethod->getMethod());
        $request->setPackageWeight($order->getWeight());
        $request->setPackages($orderShipment->getPackages());
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($shipmentStoreId);

        return $shipmentCarrier->requestToShipment($request);
    }

    /**
     * Set shipper details into request
     * @param Request $request
     * @param User $storeAdmin
     * @param DataObject $store
     * @param $shipmentStoreId
     * @param $regionCode
     * @param $originStreet
     * @return void
     */
    protected function setShipperDetails(
        Request $request,
        User $storeAdmin,
        DataObject $store,
        $shipmentStoreId,
        $regionCode,
        $originStreet
    ) {
        $originStreet2 = $this->_scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS2,
            ScopeInterface::SCOPE_STORE,
            $shipmentStoreId
        );

        $request->setShipperContactPersonName($storeAdmin->getName());
        $request->setShipperContactPersonFirstName($storeAdmin->getFirstName());
        $request->setShipperContactPersonLastName($storeAdmin->getLastName());
        $request->setShipperContactCompanyName($store->getName());
        $request->setShipperContactPhoneNumber($store->getPhone());
        $request->setShipperEmail($storeAdmin->getEmail());
        $request->setShipperAddressStreet(trim($originStreet . ' ' . $originStreet2));
        $request->setShipperAddressStreet1($originStreet);
        $request->setShipperAddressStreet2($originStreet2);
        $request->setShipperAddressCity(
            $this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
        );
        $request->setShipperAddressStateOrProvinceCode($regionCode);
        $request->setShipperAddressPostalCode(
            $this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
        );
        $request->setShipperAddressCountryCode(
            $this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
        );
    }

    /**
     * Set recipient details into request
     * @param Request $request
     * @param OrderAddress $address
     * @return void
     */
    protected function setRecipientDetails(Request $request, Address $address)
    {
        $request->setRecipientContactPersonName(trim($address->getFirstname() . ' ' . $address->getLastname()));
        $request->setRecipientContactPersonFirstName($address->getFirstname());
        $request->setRecipientContactPersonLastName($address->getLastname());
        $request->setRecipientContactCompanyName($address->getCompany());
        $request->setRecipientContactPhoneNumber($address->getTelephone());
        $request->setRecipientEmail($address->getEmail());
        $request->setRecipientAddressStreet(trim($address->getStreetLine(1) . ' ' . $address->getStreetLine(2)));
        $request->setRecipientAddressStreet1($address->getStreetLine(1));
        $request->setRecipientAddressStreet2($address->getStreetLine(2));
        $request->setRecipientAddressCity($address->getCity());
        $request->setRecipientAddressStateOrProvinceCode($address->getRegionCode() ?: $address->getRegion());
        $request->setRecipientAddressRegionCode($address->getRegionCode());
        $request->setRecipientAddressPostalCode($address->getPostcode());
        $request->setRecipientAddressCountryCode($address->getCountryId());
    }
}
