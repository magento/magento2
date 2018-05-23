<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Indexer\MultiDimensional\Dimension;

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
     * @var \Magento\Framework\Indexer\MultiDimensional\DimensionCollectionFactory
     */
    private $generalDimensionCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $configReader;

    /**
     * @param \Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProviderFactory $websiteDataProviderFactory
     * @param \Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProviderFactory $customerGroupDataProviderFactory
     * @param \Magento\Framework\Indexer\MultiDimensional\DimensionCollectionFactory $generalDimensionCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configReader
     */
    public function __construct(
        \Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProviderFactory $websiteDataProviderFactory,
        \Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProviderFactory $customerGroupDataProviderFactory,
        \Magento\Framework\Indexer\MultiDimensional\DimensionCollectionFactory $generalDimensionCollectionFactory,
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
//        $dimensionsMode = $this->configReader->read('price_dimensions');
        $dimensionsMode = 'none';
        $providers = [];

        // TODO: change strings to const
        switch ($dimensionsMode) {
            case 'none':
                break;

            case 'website':
                $providers[] = $this->websiteDataProviderFactory->create();
                break;

            case 'customer_group':
                $providers[] = $this->customerGroupDataProviderFactory->create();
                break;

            case 'website_and_customer_group':
                $providers[] = $this->websiteDataProviderFactory->create();
                $providers[] = $this->customerGroupDataProviderFactory->create();
                break;

            default:
                throw new \InvalidArgumentException(
                    sprintf('Undefined dimension name "%s".', $dimensionsMode)
                );
        }

        return $providers;
    }
}
