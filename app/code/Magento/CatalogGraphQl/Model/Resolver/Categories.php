<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\CatalogGraphQl\Model\Category\Hydrator as CategoryHydrator;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductCategories;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CustomAttributesFlattener;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resolver for category objects the product is assigned to.
 */
class Categories implements ResolverInterface, ResetAfterRequestInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var AttributesJoiner
     */
    private AttributesJoiner $attributesJoiner;

    /**
     * @var CustomAttributesFlattener
     */
    private CustomAttributesFlattener $customAttributesFlattener;

    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * @var CategoryHydrator
     */
    private CategoryHydrator $categoryHydrator;

    /**
     * @var ProductCategories
     */
    private ProductCategories $productCategories;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Products whose category ids are requested but not loaded yet, per store
     *
     * @var array<int, array<int, int>>
     */
    private array $pendingProductIds = [];

    /**
     * @var array<int, array<int, string[]>>
     */
    private array $categoryIdsByProduct = [];

    /**
     * Loaded categories per store and requested field set; false marks an id the collection did not return
     *
     * @var array<string, array<int, CategoryInterface|false>>
     */
    private array $loadedCategories = [];

    /**
     * @param CollectionFactory $collectionFactory
     * @param AttributesJoiner $attributesJoiner
     * @param CustomAttributesFlattener $customAttributesFlattener
     * @param ValueFactory $valueFactory
     * @param CategoryHydrator $categoryHydrator
     * @param ProductCategories $productCategories
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributesJoiner $attributesJoiner,
        CustomAttributesFlattener $customAttributesFlattener,
        ValueFactory $valueFactory,
        CategoryHydrator $categoryHydrator,
        ProductCategories $productCategories,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->attributesJoiner = $attributesJoiner;
        $this->customAttributesFlattener = $customAttributesFlattener;
        $this->valueFactory = $valueFactory;
        $this->categoryHydrator = $categoryHydrator;
        $this->productCategories = $productCategories;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $value['model'];
        $storeId = (int)$this->storeManager->getStore()->getId();
        $productId = (int)$product->getId();
        $categoryIds = null;
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE && in_array('orders', $info->path)) {
            $categoryIds = $product->getCategoryIds();
        } elseif (!isset($this->categoryIdsByProduct[$storeId][$productId])) {
            $this->pendingProductIds[$storeId][$productId] = $productId;
        }
        return $this->valueFactory->create(
            function () use ($categoryIds, $info, $storeId, $productId) {
                $categories = [];
                $categoryIds ??= $this->getCategoryIds($productId, $storeId);
                if (empty($categoryIds)) {
                    return [];
                }
                /** @var CategoryInterface | \Magento\Catalog\Model\Category $item */
                foreach ($this->getCategories($categoryIds, $storeId, $info) as $item) {
                    if (in_array($item->getId(), $categoryIds)) {
                        // Try to extract all requested fields from the loaded collection data
                        $categories[$item->getId()] = $this->categoryHydrator->hydrateCategory($item, true);
                        $categories[$item->getId()]['model'] = $item;
                        $requestedFields = $this->attributesJoiner->getQueryFields($info->fieldNodes[0], $info);
                        $extractedFields = array_keys($categories[$item->getId()]);
                        $foundFields = array_intersect($requestedFields, $extractedFields);
                        if (count($requestedFields) === count($foundFields)) {
                            continue;
                        }
                        // If not all requested fields were extracted from the collection, start more complex extraction
                        $categories[$item->getId()] = $this->categoryHydrator->hydrateCategory($item);
                    }
                }
                return $categories;
            }
        );
    }

    /**
     * Get category ids of the product, loading them for all pending products of the store in one query
     *
     * @param int $productId
     * @param int $storeId
     * @return string[]
     */
    private function getCategoryIds(int $productId, int $storeId): array
    {
        if (!isset($this->categoryIdsByProduct[$storeId][$productId])) {
            $productIds = $this->pendingProductIds[$storeId] ?? [];
            $productIds[$productId] = $productId;
            unset($this->pendingProductIds[$storeId]);
            $categoryIds = $this->productCategories->getCategoryIdsByProducts(array_values($productIds), $storeId);
            foreach ($productIds as $id) {
                $this->categoryIdsByProduct[$storeId][$id] = $categoryIds[$id] ?? [];
            }
        }

        return $this->categoryIdsByProduct[$storeId][$productId];
    }

    /**
     * Get categories by ids, loading every category known for the store's products in one collection
     *
     * @param array $categoryIds
     * @param int $storeId
     * @param ResolveInfo $info
     * @return CategoryInterface[]
     */
    private function getCategories(array $categoryIds, int $storeId, ResolveInfo $info): array
    {
        $requestedFields = $this->attributesJoiner->getQueryFields($info->fieldNodes[0], $info);
        sort($requestedFields);
        $key = $storeId . '|' . implode(',', $requestedFields);
        $loaded = $this->loadedCategories[$key] ?? [];
        if (array_diff($categoryIds, array_keys($loaded))) {
            $idsToLoad = array_merge($categoryIds, ...array_values($this->categoryIdsByProduct[$storeId] ?? []));
            $idsToLoad = array_diff(array_unique($idsToLoad), array_keys($loaded));
            $collection = $this->collectionFactory->create();
            $this->attributesJoiner->join($info->fieldNodes[0], $collection, $info);
            $collection->addIdFilter($idsToLoad);
            foreach ($collection as $item) {
                $loaded[$item->getId()] = $item;
            }
            $loaded += array_fill_keys($idsToLoad, false);
            $this->loadedCategories[$key] = $loaded;
        }

        return array_filter(array_intersect_key($loaded, array_flip($categoryIds)));
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->pendingProductIds = [];
        $this->categoryIdsByProduct = [];
        $this->loadedCategories = [];
    }
}
