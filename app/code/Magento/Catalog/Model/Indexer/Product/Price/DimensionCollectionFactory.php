<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Indexer\Dimension;

class DimensionCollectionFactory
{
    /**
     * @var \Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProviderFactory
     */
    private $websiteDataProviderFactory;

    /**
     * @var \Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProviderFactory
     */
    private $customerGroupDataProviderFactory;

    /**
     * @var \Magento\Framework\Indexer\DimensionCollectionFactory
     */
    private $generalDimensionCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $configReader;

    /**
     * @param \Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProviderFactory $websiteDataProviderFactory
     * @param \Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProviderFactory $customerGroupDataProviderFactory
     * @param \Magento\Framework\Indexer\DimensionCollectionFactory $generalDimensionCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configReader
     */
    public function __construct(
        \Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProviderFactory $websiteDataProviderFactory,
        \Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProviderFactory $customerGroupDataProviderFactory,
        \Magento\Framework\Indexer\DimensionCollectionFactory $generalDimensionCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $configReader
    )
    {
        $this->websiteDataProviderFactory = $websiteDataProviderFactory;
        $this->customerGroupDataProviderFactory = $customerGroupDataProviderFactory;
        $this->generalDimensionCollectionFactory = $generalDimensionCollectionFactory;
        $this->configReader = $configReader;
    }

    /**
     * @return Dimension[]
     */
    public function create()
    {
        return $this->generalDimensionCollectionFactory->create(
            [
                'dimensionDataProviders' => $this->getDataProviders()
            ]
        );
    }

    /**
     * @return Dimension[]
     */
    public function createWithAllDimensions()
    {
        return $this->generalDimensionCollectionFactory->create(
            [
                'dimensionDataProviders' => [
                    $this->websiteDataProviderFactory->create(),
                    $this->customerGroupDataProviderFactory->create()
                ]
            ]
        );
    }

    private function getDataProviders()
    {
        $providers = [];
        $dimensionsMode = $this->configReader->getValue(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE);

        switch ($dimensionsMode) {
            case null:
            case ModeSwitcher::INPUT_KEY_NONE:
                break;

            case ModeSwitcher::INPUT_KEY_WEBSITE:
                $providers[] = $this->websiteDataProviderFactory->create();
                break;

            case ModeSwitcher::INPUT_KEY_CUSTOMER_GROUP:
                $providers[] = $this->customerGroupDataProviderFactory->create();
                break;

            case ModeSwitcher::INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP:
                $providers[] = $this->websiteDataProviderFactory->create();
                $providers[] = $this->customerGroupDataProviderFactory->create();
                break;

            default:
                throw new \InvalidArgumentException(
                    sprintf('Undefined dimension mode "%s".', $dimensionsMode)
                );
        }

        return $providers;
    }
}
