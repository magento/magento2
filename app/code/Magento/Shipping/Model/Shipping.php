<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Address\RateCollectorInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Rate\CarrierResult;
use Magento\Shipping\Model\Rate\CarrierResultFactory;
use Magento\Shipping\Model\Rate\PackageResult;
use Magento\Shipping\Model\Rate\PackageResultFactory;
use Magento\Shipping\Model\Rate\Result;

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
     * @var \Magento\Shipping\Model\Rate\Result
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
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
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Framework\Math\Division
     */
    protected $mathDivision;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var RateRequestFactory
     */
    private $rateRequestFactory;

    /**
     * @var PackageResultFactory
     */
    private $packageResultFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Magento\Shipping\Model\Rate\CarrierResultFactory $rateResultFactory
     * @param \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\Math\Division $mathDivision
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param RateRequestFactory $rateRequestFactory
     * @param PackageResultFactory|null $packageResultFactory
     * @param CarrierResultFactory|null $carrierResultFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Math\Division $mathDivision,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        RateRequestFactory $rateRequestFactory = null,
        ?PackageResultFactory $packageResultFactory = null,
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
        $this->mathDivision = $mathDivision;
        $this->stockRegistry = $stockRegistry;
        $this->rateRequestFactory = $rateRequestFactory ?: ObjectManager::getInstance()->get(RateRequestFactory::class);
        $this->packageResultFactory = $packageResultFactory
            ?? ObjectManager::getInstance()->get(PackageResultFactory::class);
    }

    /**
     * Get shipping rate result model
     *
     * @return \Magento\Shipping\Model\Rate\Result|CarrierResult
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
     * @return \Magento\Shipping\Model\Config
     */
    public function getConfig()
    {
        return $this->_shippingConfig;
    }

    /**
     * Retrieve all methods for supplied shipping data
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return $this
     * @todo make it ordered
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $storeId = $request->getStoreId();
        if (!$request->getOrig()) {
            $request->setCountryId(
                $this->_scopeConfig->getValue(
                    Shipment::XML_PATH_STORE_COUNTRY_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStore()
                )
            )->setRegionId(
                $this->_scopeConfig->getValue(
                    Shipment::XML_PATH_STORE_REGION_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStore()
                )
            )->setCity(
                $this->_scopeConfig->getValue(
                    Shipment::XML_PATH_STORE_CITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStore()
                )
            )->setPostcode(
                $this->_scopeConfig->getValue(
                    Shipment::XML_PATH_STORE_ZIP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStore()
                )
            );
        }

        $limitCarrier = $request->getLimitCarrier();
        if (!$limitCarrier) {
            $carriers = $this->_scopeConfig->getValue(
                'carriers',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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
     * @throws \RuntimeException
     */
    private function prepareCarrier(string $carrierCode, RateRequest $request): AbstractCarrier
    {
        $carrier = $this->isShippingCarrierAvailable($carrierCode)
            ? $this->_carrierFactory->create($carrierCode, $request->getStoreId())
            : null;
        if (!$carrier) {
            throw new \RuntimeException('Failed to initialize carrier');
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
            throw new \RuntimeException('Cannot collect rates for given request');
        } elseif ($result instanceof Error) {
            $this->getResult()->append($result);
            throw new \RuntimeException('Error occurred while preparing a carrier');
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
        } catch (\RuntimeException $exception) {
            return $this;
        }

        /** @var Result|\Magento\Quote\Model\Quote\Address\RateResult\Error|null $result */
        $result = null;
        if ($carrier->getConfigData('shipment_requesttype')) {
            $packages = $this->composePackagesForCarrier($carrier, $request);
            if (!empty($packages)) {
                //Multiple shipments
                /** @var PackageResult $result */
                $result = $this->packageResultFactory->create();
                foreach ($packages as $weight => $packageCount) {
                    $request->setPackageWeight($weight);
                    $packageResult = $carrier->collectRates($request);
                    if (!$packageResult) {
                        return $this;
                    } else {
                        $result->appendPackageResult($packageResult, $packageCount);
                    }
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
     * @param \Magento\Shipping\Model\Carrier\AbstractCarrier $carrier
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
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

        /** @var $item \Magento\Quote\Model\Quote\Item */
        foreach ($allItems as $item) {
            if ($item->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
                && $item->getProduct()->getShipmentType()
            ) {
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

            $itemWeight = $item->getWeight();
            if ($item->getIsQtyDecimal()
                && $item->getProductType() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
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
                && $item->getProductType() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            ) {
                $qty = 1;
            }

            if (!empty($decimalItems)) {
                foreach ($decimalItems as $decimalItem) {
                    $weightItems[] = array_fill(0, $decimalItem['qty'] * $qty, $decimalItem['weight']);
                }
            } else {
                $weightItems[] = array_fill(0, $qty, $itemWeight);
            }
        }
        $fullItems = array_merge($fullItems, ...$weightItems);
        sort($fullItems);

        return $this->_makePieces($fullItems, $maxWeight);
    }

    /**
     * Make pieces
     *
     * Compose packages list based on given items, so that each package is as heavy as possible
     *
     * @param array $items
     * @param float $maxWeight
     * @return array
     */
    protected function _makePieces($items, $maxWeight)
    {
        $pieces = [];
        if (!empty($items)) {
            $sumWeight = 0;

            $reverseOrderItems = $items;
            arsort($reverseOrderItems);

            foreach ($reverseOrderItems as $key => $weight) {
                if (!isset($items[$key])) {
                    continue;
                }
                unset($items[$key]);
                $sumWeight = $weight;
                foreach ($items as $key => $weight) {
                    if ($sumWeight + $weight < $maxWeight) {
                        unset($items[$key]);
                        $sumWeight += $weight;
                    } elseif ($sumWeight + $weight > $maxWeight) {
                        $pieces[] = (string)(double)$sumWeight;
                        break;
                    } else {
                        unset($items[$key]);
                        $pieces[] = (string)(double)($sumWeight + $weight);
                        $sumWeight = 0;
                        break;
                    }
                }
            }
            if ($sumWeight > 0) {
                $pieces[] = (string)(double)$sumWeight;
            }
            $pieces = array_count_values($pieces);
        }

        return $pieces;
    }

    /**
     * Collect rates by address
     *
     * @param \Magento\Framework\DataObject $address
     * @param null|bool|array $limitCarrier
     * @return $this
     */
    public function collectRatesByAddress(\Magento\Framework\DataObject $address, $limitCarrier = null)
    {
        /** @var $request \Magento\Quote\Model\Quote\Address\RateRequest */
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

        /** @var \Magento\Store\Api\Data\StoreInterface $store */
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
     * @return bool
     */
    private function isShippingCarrierAvailable(string $carrierCode): bool
    {
        return $this->_scopeConfig->isSetFlag(
            'carriers/' . $carrierCode . '/' . $this->_availabilityConfigField,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
