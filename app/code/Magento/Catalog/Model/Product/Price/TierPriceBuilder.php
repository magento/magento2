<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Builds ProductTierPriceInterface objects
 */
class TierPriceBuilder
{
    /**
     * @var int
     */
    private $websiteId = 0;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    protected $tierPriceFactory;

    /**
     * @var ProductTierPriceExtensionFactory
     */
    private $tierPriceExtensionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param ProductTierPriceExtensionFactory $tierPriceExtensionFactory
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductTierPriceInterfaceFactory $tierPriceFactory,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager
    ) {
        $this->tierPriceFactory = $tierPriceFactory;
        $this->tierPriceExtensionFactory = $tierPriceExtensionFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Transform the raw tier prices of the product into array of ProductTierPriceInterface objects
     *
     * @param array $tierPricesRaw
     * @return ProductTierPriceInterface[]
     */
    public function buildTierPriceObjects(array $tierPricesRaw): array
    {
        $prices = [];

        foreach ($tierPricesRaw as $tierPriceRaw) {
            $prices[] = $this->createTierPriceObjectFromRawData($tierPriceRaw);
        }

        return $prices;
    }

    /**
     * Transform the raw tier price data into ProductTierPriceInterface object
     *
     * @param array $tierPriceRaw
     * @return ProductTierPriceInterface
     */
    private function createTierPriceObjectFromRawData(array $tierPriceRaw): ProductTierPriceInterface
    {
        //Find and set the website id that would be used as a fallback if the raw data does not bear it itself
        $this->setWebsiteForPriceScope();

        /** @var ProductTierPriceInterface $tierPrice */
        $tierPrice = $this->tierPriceFactory->create()
            ->setExtensionAttributes($this->tierPriceExtensionFactory->create());

        $tierPrice->setCustomerGroupId(
            isset($tierPriceRaw['cust_group']) ? $tierPriceRaw['cust_group'] : ''
        );
        $tierPrice->setValue(
            isset($tierPriceRaw['website_price']) ? $tierPriceRaw['website_price'] : $tierPriceRaw['price']
        );
        $tierPrice->setQty(
            isset($tierPriceRaw['price_qty']) ? $tierPriceRaw['price_qty'] : ''
        );
        $tierPrice->getExtensionAttributes()->setWebsiteId(
            isset($tierPriceRaw['website_id']) ? (int)$tierPriceRaw['website_id'] : $this->websiteId
        );
        if (isset($tierPriceRaw['percentage_value'])) {
            $tierPrice->getExtensionAttributes()->setPercentageValue($tierPriceRaw['percentage_value']);
        }

        return $tierPrice;
    }

    /**
     * Find and set the website id, based on the catalog price scope setting
     */
    private function setWebsiteForPriceScope()
    {
        if ($this->websiteId != 0) {
            return;
        }

        $websiteId = 0;
        $value = $this->config->getValue('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE);
        if ($value != 0) {
            $websiteId = $this->storeManager->getWebsite()->getId();
        }

        $this->websiteId = (int)$websiteId;
    }
}
