<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Address\RateCollectorInterface;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Sales\Model\Order\Shipment;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Shipping implements RateCollectorInterface
{
    /**
     * Default shipping orig for requests
     *
     * @var array
     * @since 2.0.0
     */
    protected $_orig = null;

    /**
     * Cached result
     *
     * @var \Magento\Shipping\Model\Rate\Result
     * @since 2.0.0
     */
    protected $_result = null;

    /**
     * Part of carrier xml config path
     *
     * @var string
     * @since 2.0.0
     */
    protected $_availabilityConfigField = 'active';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Shipping\Model\Config
     * @since 2.0.0
     */
    protected $_shippingConfig;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     * @since 2.0.0
     */
    protected $_carrierFactory;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     * @since 2.0.0
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequestFactory
     * @since 2.0.0
     */
    protected $_shipmentRequestFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     * @since 2.0.0
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Framework\Math\Division
     * @since 2.0.0
     */
    protected $mathDivision;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     * @since 2.0.0
     */
    protected $stockRegistry;

    /**
     * @var RateRequestFactory
     * @since 2.2.0
     */
    private $rateRequestFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\Math\Division $mathDivision
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param RateRequestFactory $rateRequestFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
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
        RateRequestFactory $rateRequestFactory = null
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_shippingConfig = $shippingConfig;
        $this->_storeManager = $storeManager;
        $this->_carrierFactory = $carrierFactory;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_shipmentRequestFactory = $shipmentRequestFactory;
        $this->_regionFactory = $regionFactory;
        $this->mathDivision = $mathDivision;
        $this->stockRegistry = $stockRegistry;
        $this->rateRequestFactory = $rateRequestFactory ?: ObjectManager::getInstance()->get(RateRequestFactory::class);
    }

    /**
     * Get shipping rate result model
     *
     * @return \Magento\Shipping\Model\Rate\Result
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setOrigData($data)
    {
        $this->_orig = $data;
    }

    /**
     * Reset cached result
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * Collect rates of given carrier
     *
     * @param string $carrierCode
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function collectCarrierRates($carrierCode, $request)
    {
        /* @var $carrier \Magento\Shipping\Model\Carrier\AbstractCarrier */
        $carrier = $this->_carrierFactory->createIfActive($carrierCode, $request->getStoreId());
        if (!$carrier) {
            return $this;
        }
        $carrier->setActiveFlag($this->_availabilityConfigField);
        $result = $carrier->checkAvailableShipCountries($request);
        if (false !== $result && !$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
            $result = $carrier->proccessAdditionalValidation($request);
        }
        /*
         * Result will be false if the admin set not to show the shipping module
         * if the delivery country is not within specific countries
         */
        if (false !== $result) {
            if (!$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                if ($carrier->getConfigData('shipment_requesttype')) {
                    $packages = $this->composePackagesForCarrier($carrier, $request);
                    if (!empty($packages)) {
                        $sumResults = [];
                        foreach ($packages as $weight => $packageCount) {
                            $request->setPackageWeight($weight);
                            $result = $carrier->collectRates($request);
                            if (!$result) {
                                return $this;
                            } else {
                                $result->updateRatePrice($packageCount);
                            }
                            $sumResults[] = $result;
                        }
                        if (!empty($sumResults) && count($sumResults) > 1) {
                            $result = [];
                            foreach ($sumResults as $res) {
                                if (empty($result)) {
                                    $result = $res;
                                    continue;
                                }
                                foreach ($res->getAllRates() as $method) {
                                    foreach ($result->getAllRates() as $resultMethod) {
                                        if ($method->getMethod() == $resultMethod->getMethod()) {
                                            $resultMethod->setPrice($method->getPrice() + $resultMethod->getPrice());
                                            continue;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $result = $carrier->collectRates($request);
                    }
                } else {
                    $result = $carrier->collectRates($request);
                }
                if (!$result) {
                    return $this;
                }
            }
            if ($carrier->getConfigData('showmethod') == 0 && $result->getError()) {
                return $this;
            }
            // sort rates by price
            if (method_exists($result, 'sortRatesByPrice') && is_callable([$result, 'sortRatesByPrice'])) {
                $result->sortRatesByPrice();
            }
            $this->getResult()->append($result);
        }
        return $this;
    }

    /**
     * Compose Packages For Carrier.
     * Divides order into items and items into parts if it's necessary
     *
     * @param \Magento\Shipping\Model\Carrier\AbstractCarrier $carrier
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return array [int, float]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function composePackagesForCarrier($carrier, $request)
    {
        $allItems = $request->getAllItems();
        $fullItems = [];

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

                $stockItem = $this->stockRegistry->getStockItem($productId, $item->getStore()->getWebsiteId());
                if ($stockItem->getIsDecimalDivided()) {
                    if ($stockItem->getEnableQtyIncrements() && $stockItem->getQtyIncrements()) {
                        $itemWeight = $itemWeight * $stockItem->getQtyIncrements();
                        $qty = round($item->getWeight() / $itemWeight * $qty);
                        $changeQty = false;
                    } else {
                        $itemWeight = $itemWeight * $item->getQty();
                        if ($itemWeight > $maxWeight) {
                            $qtyItem = floor($itemWeight / $maxWeight);
                            $decimalItems[] = ['weight' => $maxWeight, 'qty' => $qtyItem];
                            $weightItem = $this->mathDivision->getExactDivision($itemWeight, $maxWeight);
                            if ($weightItem) {
                                $decimalItems[] = ['weight' => $weightItem, 'qty' => 1];
                            }
                            $checkWeight = false;
                        } else {
                            $itemWeight = $itemWeight * $item->getQty();
                        }
                    }
                } else {
                    $itemWeight = $itemWeight * $item->getQty();
                }
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
                    $fullItems = array_merge(
                        $fullItems,
                        array_fill(0, $decimalItem['qty'] * $qty, $decimalItem['weight'])
                    );
                }
            } else {
                $fullItems = array_merge($fullItems, array_fill(0, $qty, $itemWeight));
            }
        }
        sort($fullItems);

        return $this->_makePieces($fullItems, $maxWeight);
    }

    /**
     * Make pieces
     * Compose packages list based on given items, so that each package is as heavy as possible
     *
     * @param array $items
     * @param float $maxWeight
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setCarrierAvailabilityConfigField($code = 'active')
    {
        $this->_availabilityConfigField = $code;
        return $this;
    }
}
