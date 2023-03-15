<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Math\Division as MathDivision;
use Magento\Quote\Model\Quote\Address\RateCollectorInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\Error as RateResultError;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Config as ShipmentConfig;
use Magento\Shipping\Model\Rate\CarrierResult;
use Magento\Shipping\Model\Rate\CarrierResultFactory;
use Magento\Shipping\Model\Rate\PackageResult;
use Magento\Shipping\Model\Rate\PackageResultFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\Result as RateResult;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Shipment\RequestFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use RuntimeException;

/**
 * @inheritDoc
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shipping implements RateCollectorInterface
{
    /**
     * Default shipping orig for requests
     *
     * @var array
     */
    protected $_orig = null;

    /**
     * Cached result
     *
     * @var RateResult
     */
    protected $_result = null;

    /**
     * Part of carrier xml config path
     *
     * @var string
     */
    protected $_availabilityConfigField = 'active';

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ShipmentConfig
     */
    protected $_shippingConfig;

    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @var CarrierResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequestFactory
     */
    protected $_shipmentRequestFactory;

    /**
     * @var RegionFactory
     */
    protected $_regionFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ShipmentConfig $shippingConfig
     * @param StoreManagerInterface $storeManager
     * @param CarrierFactory $carrierFactory
     * @param \Magento\Shipping\Model\Rate\CarrierResultFactory $rateResultFactory
     * @param RequestFactory $shipmentRequestFactory
     * @param RegionFactory $regionFactory
     * @param MathDivision $mathDivision
     * @param StockRegistryInterface $stockRegistry
     * @param RateRequestFactory $rateRequestFactory
     * @param PackageResultFactory|null $packageResultFactory
     * @param CarrierResultFactory|null $carrierResultFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ShipmentConfig $shippingConfig,
        StoreManagerInterface $storeManager,
        CarrierFactory $carrierFactory,
        ResultFactory $rateResultFactory,
        RequestFactory $shipmentRequestFactory,
        RegionFactory $regionFactory,
        protected readonly MathDivision $mathDivision,
        protected readonly StockRegistryInterface $stockRegistry,
        private ?RateRequestFactory $rateRequestFactory = null,
        private ?PackageResultFactory $packageResultFactory = null,
        ?CarrierResultFactory $carrierResultFactory = null
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_shippingConfig = $shippingConfig;
        $this->_storeManager = $storeManager;
        $this->_carrierFactory = $carrierFactory;
        $rateResultFactory = $carrierResultFactory ?? ObjectManager::getInstance()->get(CarrierResultFactory::class);
        $this->_rateResultFactory = $rateResultFactory;
        $this->_shipmentRequestFactory = $shipmentRequestFactory;
        $this->_regionFactory = $regionFactory;
        $this->rateRequestFactory = $rateRequestFactory ?: ObjectManager::getInstance()->get(RateRequestFactory::class);
        $this->packageResultFactory = $packageResultFactory
            ?? ObjectManager::getInstance()->get(PackageResultFactory::class);
    }

    /**
     * Get shipping rate result model
     *
     * @return RateResult|CarrierResult
     */
    public function getResult()
    {
        if (empty($this->_result)) {
            $this->_result = $this->_rateResultFactory->create();
        }
        return $this->_result;
    }

    /**
     * Set shipping orig data
     *
     * @param array $data
     * @return void
     */
    public function setOrigData($data)
    {
        $this->_orig = $data;
    }

    /**
     * Reset cached result
     *
     * @return $this
     */
    public function resetResult()
    {
        $this->getResult()->reset();
        return $this;
    }

    /**
     * Retrieve configuration model
     *
     * @return ShipmentConfig
     */
    public function getConfig()
    {
        return $this->_shippingConfig;
    }

    /**
     * Retrieve all methods for supplied shipping data
     *
     * @param RateRequest $request
     * @return $this
     * @todo make it ordered
     */
    public function collectRates(RateRequest $request)
    {
        $storeId = $request->getStoreId();
        if (!$request->getOrig()) {
            $request->setCountryId(
                $this->_scopeConfig->getValue(
                    Shipment::XML_PATH_STORE_COUNTRY_ID,
                    ScopeInterface::SCOPE_STORE,
                    $request->getStore()
                )
            )->setRegionId(
                $this->_scopeConfig->getValue(
                    Shipment::XML_PATH_STORE_REGION_ID,
                    ScopeInterface::SCOPE_STORE,
                    $request->getStore()
                )
            )->setCity(
                $this->_scopeConfig->getValue(
                    Shipment::XML_PATH_STORE_CITY,
                    ScopeInterface::SCOPE_STORE,
                    $request->getStore()
                )
            )->setPostcode(
                $this->_scopeConfig->getValue(
                    Shipment::XML_PATH_STORE_ZIP,
                    ScopeInterface::SCOPE_STORE,
                    $request->getStore()
                )
            );
        }

        $limitCarrier = $request->getLimitCarrier();
        if (!$limitCarrier) {
            $carriers = $this->_scopeConfig->getValue(
                'carriers',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            foreach ($carriers as $carrierCode => $carrierConfig) {
                $this->collectCarrierRates($carrierCode, $request);
            }
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = [$limitCarrier];
            }
            foreach ($limitCarrier as $carrierCode) {
                $carrierConfig = $this->_scopeConfig->getValue(
                    'carriers/' . $carrierCode,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                if (!$carrierConfig) {
                    continue;
                }
                $this->collectCarrierRates($carrierCode, $request);
            }
        }

        return $this;
    }

    /**
     * Prepare carrier to find rates.
     *
     * @param string $carrierCode
     * @param RateRequest $request
     * @return AbstractCarrier
     * @throws RuntimeException
     */
    private function prepareCarrier(string $carrierCode, RateRequest $request): AbstractCarrier
    {
        $carrier = $this->isShippingCarrierAvailable($carrierCode, $request->getStoreId())
            ? $this->_carrierFactory->create($carrierCode, $request->getStoreId())
            : null;
        if (!$carrier) {
            throw new RuntimeException('Failed to initialize carrier');
        }
        $carrier->setActiveFlag($this->_availabilityConfigField);
        $result = $carrier->checkAvailableShipCountries($request);
        if (false !== $result && !$result instanceof Error) {
            $result = $carrier->processAdditionalValidation($request);
        }
        if (!$result) {
            /*
             * Result will be false if the admin set not to show the shipping module
             * if the delivery country is not within specific countries
             */
            throw new RuntimeException('Cannot collect rates for given request');
        } elseif ($result instanceof Error) {
            $this->getResult()->append($result);
            throw new RuntimeException('Error occurred while preparing a carrier');
        }

        return $carrier;
    }

    /**
     * Collect rates of given carrier
     *
     * @param string $carrierCode
     * @param RateRequest $request
     * @return $this
     */
    public function collectCarrierRates($carrierCode, $request)
    {
        try {
            $carrier = $this->prepareCarrier($carrierCode, $request);
        } catch (RuntimeException $exception) {
            return $this;
        }

        /** @var Result|RateResultError|null $result */
        $result = null;
        if ($carrier->getConfigData('shipment_requesttype')) {
            $packages = $this->composePackagesForCarrier($carrier, $request);
            if (!empty($packages)) {
                //Multiple shipments
                /** @var PackageResult $result */
                $result = $this->packageResultFactory->create();
                $request->setPackages($packages);
                $packageResult = $carrier->collectRates($request);
                if (!$packageResult) {
                    return $this;
                } else {
                    $result->appendPackageResult($packageResult, 1);
                }
            }
        }
        if (!$result) {
            //One shipment for all items.
            $result = $carrier->collectRates($request);
        }

        if (!$result) {
            return $this;
        } elseif ($result instanceof Result) {
            $this->getResult()->appendResult($result, $carrier->getConfigData('showmethod') != 0);
        } else {
            $this->getResult()->append($result);
        }

        return $this;
    }

    /**
     * Compose Packages For Carrier.
     *
     * Divides order into items and items into parts if it's necessary
     *
     * @param AbstractCarrier $carrier
     * @param RateRequest $request
     * @return array [int, float]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function composePackagesForCarrier($carrier, $request)
    {
        $allItems = $request->getAllItems();
        $fullItems = [];
        $weightItems = [];

        $maxWeight = (double)$carrier->getConfigData('max_package_weight');

        /** @var QuoteItem $item */
        foreach ($allItems as $item) {
            if ($item->getProductType() == ProductType::TYPE_BUNDLE
                && $item->getProduct()->getShipmentType()
            ) {
                continue;
            }

            if ($item->getFreeShipping()) {
                continue;
            }

            $qty = $item->getQty();
            $changeQty = true;
            $checkWeight = true;
            $decimalItems = [];

            if ($item->getParentItem()) {
                if (!$item->getParentItem()->getProduct()->getShipmentType()) {
                    continue;
                }
                $qty = $item->getIsQtyDecimal()
                    ? $item->getParentItem()->getQty()
                    : $item->getParentItem()->getQty() * $item->getQty();
            }

            $itemWeight = (float) $item->getWeight();
            if ($item->getIsQtyDecimal()
                && $item->getProductType() != ProductType::TYPE_BUNDLE
            ) {
                $productId = $item->getProduct()->getId();
                $itemWeightWhole = $itemWeight * $item->getQty();
                $stockItem = $this->stockRegistry->getStockItem($productId, $item->getStore()->getWebsiteId());
                if ($stockItem->getIsDecimalDivided()) {
                    if ($stockItem->getEnableQtyIncrements() && $stockItem->getQtyIncrements()) {
                        $itemWeightWhole = $itemWeight * $stockItem->getQtyIncrements();
                        $qty = round($item->getWeight() / $itemWeightWhole * $qty);
                        $changeQty = false;
                    } elseif ($itemWeightWhole > $maxWeight) {
                        $itemWeightWhole = $itemWeight;
                        $qtyItem = floor($itemWeight / $maxWeight);
                        $decimalItems[] = ['weight' => $maxWeight, 'qty' => $qtyItem];
                        $weightItem = $this->mathDivision->getExactDivision($itemWeight, $maxWeight);
                        if ($weightItem) {
                            $decimalItems[] = ['weight' => $weightItem, 'qty' => 1];
                        }
                        $checkWeight = false;
                    }
                }
                $itemWeight = $itemWeightWhole;
            }

            if ($checkWeight && $maxWeight && $itemWeight > $maxWeight) {
                return [];
            }

            if ($changeQty
                && !$item->getParentItem()
                && $item->getIsQtyDecimal()
                && $item->getProductType() != ProductType::TYPE_BUNDLE
            ) {
                $qty = 1;
            }

            if (!empty($decimalItems)) {
                foreach ($decimalItems as $decimalItem) {
                    $weightItems[] = array_fill(
                        0,
                        $decimalItem['qty'] * $qty,
                        [
                            'weight' => $decimalItem['weight'],
                            'price' => $item->getBasePrice()
                        ]
                    );
                }
            } else {
                $weightItems[] = array_fill(
                    0,
                    $qty,
                    [
                        'weight' => $itemWeight,
                        'price' => $item->getBasePrice()
                    ]
                );
            }
        }
        $fullItems = array_merge($fullItems, ...$weightItems);

        return $this->_makePieces($fullItems, $maxWeight);
    }

    /**
     * Compose order items into packages using first fit decreasing algorithm
     *
     * @param array $orderItems
     * @param float $maxPackageWeight
     * @return array
     */
    protected function _makePieces(array $orderItems, float $maxPackageWeight): array
    {
        $packages = [];

        usort($orderItems, function ($a, $b) {
            return $b['weight'] <=> $a['weight'];
        });

        for ($i = 0;; $i++) {
            if (!count($orderItems)) {
                break;
            }

            $packages[$i]['weight'] = 0;
            $packages[$i]['price'] = 0;

            foreach ($orderItems as $k => $orderItem) {
                if ($orderItem['weight'] <= $maxPackageWeight - $packages[$i]['weight']) {
                    $packages[$i]['weight'] += $orderItem['weight'];
                    $packages[$i]['price'] += $orderItem['price'];
                    unset($orderItems[$k]);
                }
            }
        }

        return $packages;
    }

    /**
     * Collect rates by address
     *
     * @param DataObject $address
     * @param null|bool|array $limitCarrier
     * @return $this
     */
    public function collectRatesByAddress(DataObject $address, $limitCarrier = null)
    {
        /** @var $request RateRequest */
        $request = $this->rateRequestFactory->create();
        $request->setAllItems($address->getAllItems());
        $request->setDestCountryId($address->getCountryId());
        $request->setDestRegionId($address->getRegionId());
        $request->setDestPostcode($address->getPostcode());
        $request->setPackageValue($address->getBaseSubtotal());
        $request->setPackageValueWithDiscount($address->getBaseSubtotalWithDiscount());
        $request->setPackageWeight($address->getWeight());
        $request->setFreeMethodWeight($address->getFreeMethodWeight());
        $request->setPackageQty($address->getItemQty());

        /** @var StoreInterface $store */
        $store = $this->_storeManager->getStore();
        $request->setStoreId($store->getId());
        $request->setWebsiteId($store->getWebsiteId());
        $request->setBaseCurrency($store->getBaseCurrency());
        $request->setPackageCurrency($store->getCurrentCurrency());
        $request->setLimitCarrier($limitCarrier);

        $request->setBaseSubtotalInclTax($address->getBaseSubtotalInclTax());

        return $this->collectRates($request);
    }

    /**
     * Set part of carrier xml config path
     *
     * @param string $code
     * @return $this
     */
    public function setCarrierAvailabilityConfigField($code = 'active')
    {
        $this->_availabilityConfigField = $code;
        return $this;
    }

    /**
     * Checks availability of carrier.
     *
     * @param string $carrierCode
     * @param null|int $storeId
     * @return bool
     */
    private function isShippingCarrierAvailable(string $carrierCode, ?int $storeId = null): bool
    {
        return $this->_scopeConfig->isSetFlag(
            'carriers/' . $carrierCode . '/' . $this->_availabilityConfigField,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
