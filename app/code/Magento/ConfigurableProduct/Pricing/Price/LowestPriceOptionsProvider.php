<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Group\RetrieverInterface as CustomerGroupRetriever;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Retrieve list of products where each product contains lower price than others at least for one possible price type
 */
class LowestPriceOptionsProvider implements LowestPriceOptionsProviderInterface
{
    private const PRELOAD_BATCH_SIZE = 100;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var LinkedProductSelectBuilderInterface
     */
    private $linkedProductSelectBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Key is product id and store id. Value is array of prepared linked products
     *
     * @var array
     */
    private $linkedProductMap;

    /**
     * Configurable products of the current listing whose linked products are resolved together on first use
     *
     * @var array
     */
    private array $pendingParents = [];

    /**
     * @var int|null
     */
    private ?int $pendingCustomerGroupId = null;

    /**
     * @var CustomerGroupRetriever|null
     */
    private ?CustomerGroupRetriever $customerGroupRetriever;

    /**
     * @param ResourceConnection $resourceConnection
     * @param LinkedProductSelectBuilderInterface $linkedProductSelectBuilder
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerGroupRetriever|null $customerGroupRetriever
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LinkedProductSelectBuilderInterface $linkedProductSelectBuilder,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        ?CustomerGroupRetriever $customerGroupRetriever = null
    ) {
        $this->resource = $resourceConnection;
        $this->linkedProductSelectBuilder = $linkedProductSelectBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->customerGroupRetriever = $customerGroupRetriever;
    }

    /**
     * @inheritdoc
     */
    public function getProducts(ProductInterface $product)
    {
        $productId = $product->getId();
        $storeId = $product->getStoreId() ?: $this->storeManager->getStore()->getId();
        $key = $storeId . '-' . $productId;
        if (!isset($this->linkedProductMap[$key]) && isset($this->pendingParents[$key])) {
            $this->loadPendingChunk($key);
        }
        if (!isset($this->linkedProductMap[$key])) {
            $productIds = $this->resource->getConnection()->fetchCol(
                '(' . implode(') UNION (', $this->linkedProductSelectBuilder->build($productId, $storeId)) . ')'
            );

            $this->linkedProductMap[$key] = $this->collectionFactory->create()
                ->addAttributeToSelect(
                    ['price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id']
                )
                ->addIdFilter($productIds)
                ->getItems();
        }
        return $this->linkedProductMap[$key];
    }

    /**
     * Register products whose lowest price options are resolved with one query on the first getProducts() call
     *
     * @param ProductInterface[] $products
     * @return void
     */
    public function preloadProducts(array $products): void
    {
        $parents = [];
        foreach ($products as $product) {
            if ($product->getTypeId() !== Configurable::TYPE_CODE) {
                continue;
            }
            $productId = $product->getId();
            $storeId = $product->getStoreId() ?: $this->storeManager->getStore()->getId();
            $key = $storeId . '-' . $productId;
            if (!isset($this->linkedProductMap[$key])) {
                $parents[$key] = [(int)$productId, (int)$storeId];
            }
        }
        $this->pendingParents = count($parents) > 1 ? $parents : [];
        $this->pendingCustomerGroupId = $this->pendingParents ? $this->getCustomerGroupId() : null;
    }

    /**
     * Resolve the pending chunk that holds the key, if it was registered for the current customer group
     *
     * @param string $key
     * @return void
     */
    private function loadPendingChunk(string $key): void
    {
        if ($this->pendingCustomerGroupId !== $this->getCustomerGroupId()) {
            $this->pendingParents = [];
            return;
        }
        foreach (array_chunk($this->pendingParents, self::PRELOAD_BATCH_SIZE, true) as $chunk) {
            if (isset($chunk[$key])) {
                $this->pendingParents = array_diff_key($this->pendingParents, $chunk);
                $this->preloadChunk($chunk);
                return;
            }
        }
    }

    /**
     * Customer group the linked product selects are built for
     *
     * @return int
     */
    private function getCustomerGroupId(): int
    {
        $this->customerGroupRetriever ??= ObjectManager::getInstance()->get(CustomerGroupRetriever::class);

        return (int)$this->customerGroupRetriever->getCustomerGroupId();
    }

    /**
     * Load linked products for a chunk of parents keyed the same way as in getProducts()
     *
     * @param array $parents
     * @return void
     */
    private function preloadChunk(array $parents): void
    {
        $keys = array_keys($parents);
        $selects = [];
        foreach (array_values($parents) as $index => [$productId, $storeId]) {
            foreach ($this->linkedProductSelectBuilder->build($productId, $storeId) as $select) {
                $selects[] = $select->columns(['parent_index' => new \Zend_Db_Expr((string)$index)]);
            }
        }

        $childIdsByKey = array_fill_keys($keys, []);
        $childIds = [];
        if ($selects) {
            // Numeric fetch: linked selects may repeat column names, the child id is the first column
            $rows = $this->resource->getConnection()->fetchAll(
                '(' . implode(') UNION (', $selects) . ')',
                [],
                \Zend_Db::FETCH_NUM
            );
            foreach ($rows as $row) {
                $childId = (int)reset($row);
                $childIdsByKey[$keys[(int)end($row)]][$childId] = true;
                $childIds[$childId] = $childId;
            }
        }

        $items = $this->collectionFactory->create()
            ->addAttributeToSelect(
                ['price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id']
            )
            ->addIdFilter(array_values($childIds))
            ->getItems();

        foreach ($childIdsByKey as $key => $linkedIds) {
            $this->linkedProductMap[$key] = array_intersect_key($items, $linkedIds);
        }
    }
}
