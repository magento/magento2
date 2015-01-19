<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Attribute;

use Magento\Framework\Parse\Zip;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\TaxClassKeyInterface;

/**
 * Tax attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Tax extends \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
{
    /**
     * Maximum number of tax rates per product supported by google shopping api
     */
    const RATES_MAX = 100;

    /**
     * @var \Magento\Tax\Helper\Data|null
     */
    protected $_taxData = null;

    /**
     * Config
     *
     * @var \Magento\GoogleShopping\Model\Config
     */
    protected $_config;

    /**
     * Tax Rate Management
     *
     * @var \Magento\Tax\Api\TaxRateManagementInterface
     */
    protected $_taxRateManagement;

    /**
     * Tax Calculation Service
     *
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    protected $_taxCalculationService;

    /**
     * Quote Details Builder
     *
     * @var \Magento\Tax\Api\Data\QuoteDetailsDataBuilder
     */
    protected $_quoteDetailsBuilder;

    /**
     * Quote Details Item Builder
     *
     * @var \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder
     */
    protected $_quoteDetailsItemBuilder;

    /**
     * Default customer tax classId
     *
     * @var int
     */
    protected $_defaultCustomerTaxClassId;

    /**
     * Region  factory
     *
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\GoogleShopping\Helper\Data $googleShoppingHelper
     * @param \Magento\GoogleShopping\Helper\Product $gsProduct
     * @param \Magento\Catalog\Model\Product\CatalogPrice $catalogPrice
     * @param \Magento\GoogleShopping\Model\Resource\Attribute $resource
     * @param \Magento\GoogleShopping\Model\Config $config
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Api\TaxRateManagementInterface $taxRateManagement
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param \Magento\Tax\Api\Data\QuoteDetailsDataBuilder $quoteDetailsBuilder
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $quoteDetailsItemBuilder
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\GoogleShopping\Helper\Data $googleShoppingHelper,
        \Magento\GoogleShopping\Helper\Product $gsProduct,
        \Magento\Catalog\Model\Product\CatalogPrice $catalogPrice,
        \Magento\GoogleShopping\Model\Resource\Attribute $resource,
        \Magento\GoogleShopping\Model\Config $config,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Tax\Api\TaxRateManagementInterface $taxRateManagement,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsDataBuilder $quoteDetailsBuilder,
        \Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder $quoteDetailsItemBuilder,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_config = $config;
        $this->_taxData = $taxData;
        $this->_taxRateManagement = $taxRateManagement;
        $this->_taxCalculationService = $taxCalculationService;
        $this->_quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->_quoteDetailsItemBuilder = $quoteDetailsItemBuilder;
        $this->_regionFactory = $regionFactory;
        $this->groupManagement = $groupManagement;
        parent::__construct(
            $context,
            $registry,
            $productFactory,
            $googleShoppingHelper,
            $gsProduct,
            $catalogPrice,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Set current attribute to entry (for specified product)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @return \Magento\Framework\Gdata\Gshopping\Entry
     * @throws \Magento\Framework\Model\Exception
     */
    public function convertAttribute($product, $entry)
    {
        $entry->cleanTaxes();
        if ($this->_taxData->getConfig()->priceIncludesTax()) {
            return $entry;
        }

        $defaultCustomerTaxClassId = $this->_getDefaultCustomerTaxClassId($product->getStoreId());
        $rates = $this->_taxRateManagement->getRatesByCustomerAndProductTaxClassId(
            $defaultCustomerTaxClassId,
            $product->getTaxClassId()
        );
        $targetCountry = $this->_config->getTargetCountry($product->getStoreId());
        $ratesTotal = 0;
        foreach ($rates as $rate) {
            $countryId = $rate->getTaxCountryId();
            $postcode = $rate->getTaxPostcode();
            if ($targetCountry == $countryId) {
                $regions = $this->_getRegionsByRegionId($rate->getTaxRegionId(), $postcode);
                $ratesTotal += count($regions);
                if ($ratesTotal > self::RATES_MAX) {
                    throw new \Magento\Framework\Model\Exception(
                        __("Google shopping only supports %1 tax rates per product", self::RATES_MAX)
                    );
                }
                foreach ($regions as $region) {
                    $adjustments = $product->getPriceInfo()->getAdjustments();
                    if (array_key_exists('tax', $adjustments)) {
                        $taxIncluded = true;
                    } else {
                        $taxIncluded = false;
                    }

                    $quoteDetailsItemDataArray = [
                        'code' => $product->getSku(),
                        'type' => 'product',
                        'tax_class_key' => [
                            TaxClassKeyInterface::KEY_TYPE => TaxClassKeyInterface::TYPE_ID,
                            TaxClassKeyInterface::KEY_VALUE => $product->getTaxClassId(),
                        ],
                        'unit_price' => $product->getPrice(),
                        'quantity' => 1,
                        'tax_included' => $taxIncluded,
                        'short_description' => $product->getName(),
                    ];

                    $billingAddressDataArray = [
                        'country_id' => $countryId,
                        'region' => ['region_id' => $rate->getTaxRegionId()],
                        'postcode' => $postcode,
                    ];

                    $shippingAddressDataArray = [
                        'country_id' => $countryId,
                        'region' => ['region_id' => $rate->getTaxRegionId()],
                        'postcode' => $postcode,
                    ];

                    $quoteDetailsDataArray = [
                        'billing_address' => $billingAddressDataArray,
                        'shipping_address' => $shippingAddressDataArray,
                        'customer_tax_class_key' => [
                            TaxClassKeyInterface::KEY_TYPE => TaxClassKeyInterface::TYPE_ID,
                            TaxClassKeyInterface::KEY_VALUE => $defaultCustomerTaxClassId,
                        ],
                        'items' => [
                            $quoteDetailsItemDataArray,
                        ],
                    ];

                    $quoteDetailsObject = $this->_quoteDetailsBuilder
                        ->populateWithArray($quoteDetailsDataArray)
                        ->create();

                    $taxDetails = $this->_taxCalculationService
                        ->calculateTax($quoteDetailsObject, $product->getStoreId());

                    $taxRate = ($taxDetails->getTaxAmount() / $taxDetails->getSubtotal()) * 100;

                    $entry->addTax(
                        [
                            'tax_rate' => $taxRate,
                            'tax_country' => $countryId,
                            'tax_region' => $region,
                        ]
                    );
                }
            }
        }

        return $entry;
    }

    /**
     * Fetch default customer tax classId
     *
     * @param null|Store|string|int $store
     * @return int
     */
    private function _getDefaultCustomerTaxClassId($store = null)
    {
        if (is_null($this->_defaultCustomerTaxClassId)) {
            // Not catching the exception here since default group is expected
            $defaultCustomerGroup = $this->groupManagement->getDefaultGroup($store);
            $this->_defaultCustomerTaxClassId = $defaultCustomerGroup->getTaxClassId();
        }
        return $this->_defaultCustomerTaxClassId;
    }

    /**
     * Get regions by region ID
     *
     * @param int $regionId
     * @param string $postalCode
     * @return String[]
     */
    private function _getRegionsByRegionId($regionId, $postalCode)
    {
        $regions = [];
        $regionCode = $this->_regionFactory->create()->load($regionId)->getCode();
        if (!is_null($regionCode)) {
            $regions = Zip::parseRegions($regionCode, $postalCode);
        }
        return $regions;
    }
}
