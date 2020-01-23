<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Class LinkedProductSelectBuilderByIndexPrice
 *
 * Provide Select object for retrieve product id by index price.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class LinkedProductSelectBuilderByIndexPrice implements LinkedProductSelectBuilderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var BaseSelectProcessorInterface
     */
    private $baseSelectProcessor;

    /**
     * @var IndexScopeResolverInterface|null
     */
    private $priceTableResolver;

    /**
     * @var DimensionFactory|null
     */
    private $dimensionFactory;

    /**
     * LinkedProductSelectBuilderByIndexPrice constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param BaseSelectProcessorInterface|null $baseSelectProcessor
     * @param IndexScopeResolverInterface|null $priceTableResolver
     * @param DimensionFactory|null $dimensionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        BaseSelectProcessorInterface $baseSelectProcessor = null,
        IndexScopeResolverInterface $priceTableResolver = null,
        DimensionFactory $dimensionFactory = null
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->metadataPool = $metadataPool;
        $this->baseSelectProcessor = (null !== $baseSelectProcessor)
            ? $baseSelectProcessor : ObjectManager::getInstance()->get(BaseSelectProcessorInterface::class);
        $this->priceTableResolver = $priceTableResolver
            ?? ObjectManager::getInstance()->get(IndexScopeResolverInterface::class);
        $this->dimensionFactory = $dimensionFactory ?? ObjectManager::getInstance()->get(DimensionFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function build(int $productId, int $storeId) : array
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $productTable = $this->resource->getTableName('catalog_product_entity');
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        $priceSelect = $this->resource->getConnection()->select()
            ->from(['parent' => $productTable], '')
            ->joinInner(
                ['link' => $this->resource->getTableName('catalog_product_relation')],
                "link.parent_id = parent.$linkField",
                []
            )->joinInner(
                [BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS => $productTable],
                sprintf('%s.entity_id = link.child_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                ['entity_id']
            )->joinInner(
                [
                    't' => $this->priceTableResolver->resolve('catalog_product_index_price', [
                        $this->dimensionFactory->create(WebsiteDimensionProvider::DIMENSION_NAME, (string)$websiteId),
                        $this->dimensionFactory->create(
                            CustomerGroupDimensionProvider::DIMENSION_NAME,
                            (string)$customerGroupId
                        ),
                    ])
                ],
                sprintf('t.entity_id = %s.entity_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                []
            )->where('parent.entity_id = ?', $productId)
            ->where('t.website_id = ?', $websiteId)
            ->where('t.customer_group_id = ?', $customerGroupId)
            ->order('t.min_price ' . Select::SQL_ASC)
            ->order(BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS . '.' . $linkField . ' ' . Select::SQL_ASC)
            ->limit(1);
        $priceSelect = $this->baseSelectProcessor->process($priceSelect);

        return [$priceSelect];
    }
}
