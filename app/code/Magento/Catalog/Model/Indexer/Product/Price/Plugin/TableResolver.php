<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Indexer\Dimension;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

/**
 * Replace catalog_product_index_price table name on the table name segmented per dimension.
 * Used only for backward compatibility
 */
class TableResolver
{
    /**
     * @var IndexScopeResolverInterface
     */
    private $priceTableResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var DimensionModeConfiguration
     */
    private $dimensionModeConfiguration;

    /**
     * @param IndexScopeResolverInterface $priceTableResolver
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param DimensionFactory $dimensionFactory
     * @param DimensionModeConfiguration $dimensionModeConfiguration
     */
    public function __construct(
        IndexScopeResolverInterface $priceTableResolver,
        StoreManagerInterface $storeManager,
        Context $context,
        DimensionFactory $dimensionFactory,
        DimensionModeConfiguration $dimensionModeConfiguration
    ) {
        $this->priceTableResolver = $priceTableResolver;
        $this->storeManager = $storeManager;
        $this->httpContext = $context;
        $this->dimensionFactory = $dimensionFactory;
        $this->dimensionModeConfiguration = $dimensionModeConfiguration;
    }

    /**
     * Replacing catalog_product_index_price table name on the table name segmented per dimension.
     *
     * @param ResourceConnection $subject
     * @param string $result
     * @param string|string[] $tableName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetTableName(
        ResourceConnection $subject,
        string $result,
        $tableName
    ) {
        if (!is_array($tableName)
            && $tableName === 'catalog_product_index_price'
            && $this->dimensionModeConfiguration->getDimensionConfiguration()
        ) {
            return $this->priceTableResolver->resolve('catalog_product_index_price', $this->getDimensions());
        }

        return $result;
    }

    /**
     * @return Dimension[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getDimensions(): array
    {
        $dimensions = [];
        foreach ($this->dimensionModeConfiguration->getDimensionConfiguration() as $dimensionName) {
            if ($dimensionName === WebsiteDimensionProvider::DIMENSION_NAME) {
                $dimensions[] = $this->createDimensionFromWebsite();
            }
            if ($dimensionName === CustomerGroupDimensionProvider::DIMENSION_NAME) {
                $dimensions[] = $this->createDimensionFromCustomerGroup();
            }
        }

        return $dimensions;
    }

    /**
     * @return Dimension
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createDimensionFromWebsite(): Dimension
    {
        $storeKey = $this->httpContext->getValue(StoreManagerInterface::CONTEXT_STORE);
        return $this->dimensionFactory->create(
            WebsiteDimensionProvider::DIMENSION_NAME,
            (string)$this->storeManager->getStore($storeKey)->getWebsiteId()
        );
    }

    /**
     * @return Dimension
     */
    private function createDimensionFromCustomerGroup(): Dimension
    {
        return $this->dimensionFactory->create(
            CustomerGroupDimensionProvider::DIMENSION_NAME,
            (string)$this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)
        );
    }
}
