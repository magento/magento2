<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

/**
 * Generates a multi-level category tree using a configurable fanout array
 * and assigns random products to leaf categories.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CategoryTreeWithProducts implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'category_identifier' => 'CategoryBulk%uniqid%',
        'category_count'      => 10,
        'product_identifier'  => 'ProductBulk%uniqid%',
        'product_count'       => 0,
        'depth'               => 1,
        'fanout'              => [],
        'root_id'             => null,
    ];

    private AdapterInterface $connection;
    private string $categoryProductTable;

    public function __construct(
        private readonly ProcessorInterface $dataProcessor,
        private readonly DataMerger $dataMerger,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly CategoryFactory $categoryFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductFactory $productFactory,
        private readonly ProductResource $productResource,
        ResourceConnection $resource
    ) {
        $this->connection = $resource->getConnection();
        $this->categoryProductTable = $resource->getTableName('catalog_category_product');
    }

    /**
     * Execute fixture.
     */
    public function apply(array $data = []): ?DataObject
    {
        $data               = $this->prepareData($data);
        $productIdentifier  = $data['product_identifier'];
        $productsCount      = (int)$data['product_count'];
        $categoryIdentifier = $data['category_identifier'];
        $categoriesCount    = (int)$data['category_count'];
        $depth              = (int)$data['depth'];
        $fanoutInput        = $data['fanout'];
        $requestedRootId    = $data['root_id'];

        if ($depth < 0 || $depth > 5) {
            throw new \RuntimeException("Parameter 'depth' must be between 0 and 5.");
        }

        /**
         * Resolve root_id
         */
        if ($requestedRootId === null) {
            $rootId = (int)$this->storeManager->getStore()->getRootCategoryId();
        } else {
            try {
                $root = $this->categoryRepository->get((int)$requestedRootId);
                $rootId = (int)$root->getId();
            } catch (\Exception $e) {
                throw new \RuntimeException("Invalid root_id '{$requestedRootId}': " . $e->getMessage());
            }
        }

        /** Compute fanout */
        $fanout = $this->computeFanout($categoriesCount, $depth, $fanoutInput);

        /** Create products */
        $products = $productsCount > 0
            ? $this->createProducts($productsCount, $productIdentifier)
            : [];

        $leafCategories   = [];
        $parentCategories = [];
        $levelParents     = [];

        /* ---------------- LEVEL 0 ---------------- */
        $levelParents[0] = [];
        foreach (range(1, $fanout[0]) as $i) {
            $levelParents[0][] = $this->createCategoryNode(
                "{$categoryIdentifier}_l0_{$rootId}_{$i}",
                $rootId,
                ($depth === 0),
                $products,
                $leafCategories,
                $parentCategories
            );
        }
        if (count($fanout) > 1) {
            /* ---------------- LEVELS 1 → depth ---------------- */
            for ($level = 1; $level <= $depth; $level++) {
                $levelParents[$level] = [];
                foreach ($levelParents[$level - 1] as $parentId) {
                    foreach (range(1, $fanout[$level]) as $i) {
                        $levelParents[$level][] = $this->createCategoryNode(
                            "{$categoryIdentifier}_l{$level}_{$parentId}_{$i}",
                            $parentId,
                            ($level === $depth),
                            $products,
                            $leafCategories,
                            $parentCategories
                        );
                    }
                }
            }
        }
        return $this->finalize(
            $categoriesCount,
            $categoryIdentifier,
            $products,
            $leafCategories,
            $parentCategories
        );
    }

    /**
     * Compute fanout
     */
    private function computeFanout(int $total, int $depth, array $fanout): array
    {
        $computed = [];
        if (count($fanout) && array_sum($fanout) > $total) {
            $computed[] = $total;
            return $computed;
        }
        $levels = $depth + 1;
        for ($i = 0; $i < $levels; $i++) {
            if (isset($fanout[$i]) && $fanout[$i] > 0) {
                $computed[$i] = (int)$fanout[$i];
                continue;
            }
            // AUTO distribute
            $computed[$i] = max(1, (int)floor(pow($total, 1 / $levels)));
        }

        return $computed;
    }

    private function finalize(
        int $categoriesCount,
        string $identifier,
        array $products,
        array $leafCategories,
        array $parentCategories
    ): DataObject {

        $total = count($parentCategories) + count($leafCategories);
        $missing = max(0, $categoriesCount - $total);

        for ($i = 1; $i <= $missing; $i++) {
            $randomParentId = $parentCategories[random_int(0, count($parentCategories) - 1)];

            $this->createCategoryNode(
                "{$identifier}_extra_{$randomParentId}_{$i}",
                $randomParentId,
                true,
                $products,
                $leafCategories,
                $parentCategories
            );
        }

        return new DataObject([
            'products' => $products,
            'leaf_categories' => $leafCategories,
            'all_categories' => array_merge($parentCategories, $leafCategories),
        ]);
    }

    /** Create products */
    private function createProducts(int $count, string $prefix): array
    {
        $ids = [];

        for ($i = 1; $i <= $count; $i++) {
            $product = $this->productFactory->create();
            $product->setTypeId('simple')
                ->setAttributeSetId(4)
                ->setSku("{$prefix}_{$i}")
                ->setName("Bulk Test Product {$i}")
                ->setPrice(10 + $i)
                ->setVisibility(4)
                ->setStatus(1);

            $this->productResource->save($product);
            $ids[] = (int)$product->getId();
        }

        return $ids;
    }

    /** Create category node */
    private function createCategoryNode(
        string $name,
        int $parentId,
        bool $isLeaf,
        array $products,
        array &$leafCategories,
        array &$parentCategories
    ): int {

        $cat = $this->categoryFactory->create();
        $cat->setName($name)
            ->setIsActive(true)
            ->setIsAnchor(1)
            ->setParentId($parentId);

        $this->categoryRepository->save($cat);

        $id = (int)$cat->getId();

        if ($isLeaf && count($products)) {
            $this->assignRandomProductsToLeaf($id, $products);
            $leafCategories[] = $id;
        } else {
            $parentCategories[] = $id;
        }

        return $id;
    }

    /** Assign products to leaf category */
    private function assignRandomProductsToLeaf(int $catId, array $products): void
    {
        $count = random_int(1, 5);
        $selected = [];

        for ($i = 0; $i < $count; $i++) {
            $selected[] = $products[random_int(0, count($products) - 1)];
        }

        $selected = array_unique($selected);

        $rows = [];
        foreach ($selected as $pid) {
            $rows[] = [
                'category_id' => $catId,
                'product_id'  => $pid,
                'position'    => 0
            ];
        }

        if ($rows) {
            $this->connection->insertMultiple($this->categoryProductTable, $rows);
        }
    }

    private function prepareData(array $data): array
    {
        return $this->dataProcessor->process(
            $this,
            $this->dataMerger->merge(self::DEFAULT_DATA, $data)
        );
    }
}
