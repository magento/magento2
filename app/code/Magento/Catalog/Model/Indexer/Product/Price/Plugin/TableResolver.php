<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Indexer\Dimension;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProvider;
use Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProvider;

/**
 * Class that replace catalog_product_index_price table name on the table name segmented per dimension
 */
class TableResolver
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

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
     * @param ScopeConfigInterface $scopeConfig
     * @param IndexScopeResolverInterface $priceTableResolver
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        IndexScopeResolverInterface $priceTableResolver,
        StoreManagerInterface $storeManager,
        Context $context,
        DimensionFactory $dimensionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->priceTableResolver = $priceTableResolver;
        $this->storeManager = $storeManager;
        $this->httpContext = $context;
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * replacing catalog_product_index_price table name on the table name segmented per dimension
     * @param ResourceConnection $subject
     * @param string $result
     * @param string|string[] $modelEntity
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return string
     */
    public function afterGetTableName(
        ResourceConnection $subject,
        string $result,
        $modelEntity
    ) {
        if (!is_array($modelEntity)
            && $modelEntity === 'catalog_product_index_price'
            && $this->getMode() !== ModeSwitcher::INPUT_KEY_NONE
        ) {
            return $this->priceTableResolver->resolve('catalog_product_index_price', $this->getDimensions());
        }
        return $result;
    }

    private function getMode(): string
    {
        return $this->scopeConfig->getValue(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE);
    }

    private function getDimensions(): array
    {
        switch ($this->getMode()) {
            case ModeSwitcher::INPUT_KEY_WEBSITE:
                $return = [
                    $this->createDimensionFromWebsite()
                ];
                break;
            case ModeSwitcher::INPUT_KEY_CUSTOMER_GROUP:
                $return = [
                    $this->createDimensionFromCustomerGroup()
                ];
                break;
            case ModeSwitcher::INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP:
                $return = [
                    $this->createDimensionFromWebsite(),
                    $this->createDimensionFromCustomerGroup()
                ];
                break;
            default:
                $return = [];
        }
        return $return;
    }

    private function createDimensionFromWebsite(): Dimension
    {
        $storeKey = $this->httpContext->getValue(StoreManagerInterface::CONTEXT_STORE);
        return $this->dimensionFactory->create(
            WebsiteDataProvider::DIMENSION_NAME,
            (string)$this->storeManager->getStore($storeKey)->getWebsiteId()
        );
    }

    private function createDimensionFromCustomerGroup(): Dimension
    {
        return $this->dimensionFactory->create(
            CustomerGroupDataProvider::DIMENSION_NAME,
            (string)$this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)
        );
    }
}
