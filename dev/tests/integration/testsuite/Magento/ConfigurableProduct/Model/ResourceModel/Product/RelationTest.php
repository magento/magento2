<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests Catalog Product Relation resource model.
 *
 * @see Relation
 */
class RelationTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Relation
     */
    private $model;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(Relation::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
    }

    /**
     * Tests that getRelationsByChildren will return parent products entity ids of child products entity ids.
     *
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product with:{"sku":"simple_10"} as:p1
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product with:{"sku":"simple_20"} as:p2
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product with:{"sku":"simple_30"} as:p3
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product with:{"sku":"simple_40"} as:p4
     * @magentoDataFixture Magento\ConfigurableProduct\Test\Fixture\Attribute as:attr
     * @magentoDataFixture Magento\ConfigurableProduct\Test\Fixture\Product as:conf1
     * @magentoDataFixture Magento\ConfigurableProduct\Test\Fixture\Product as:conf2
     * @magentoDataFixtureDataProvider {"conf1":{"sku":"conf1","_options":["$attr$"],"_links":["$p1$","$p2$"]}}
     * @magentoDataFixtureDataProvider {"conf2":{"sku":"conf2","_options":["$attr$"],"_links":["$p3$","$p4$"]}}
     */
    public function testGetRelationsByChildren(): void
    {
        $childSkusOfParentSkus = [
            'conf1' => ['simple_10', 'simple_20'],
            'conf2' => ['simple_30', 'simple_40'],
        ];
        $configurableSkus = [
            'conf1',
            'conf2',
            'simple_10',
            'simple_20',
            'simple_30',
            'simple_40',
        ];
        $configurableIdsOfSkus = [];

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', $configurableSkus, 'in')
            ->create();
        $configurableProducts = $this->productRepository->getList($searchCriteria)
            ->getItems();

        $childIds = [];

        foreach ($configurableProducts as $product) {
            $configurableIdsOfSkus[$product->getSku()] = $product->getId();

            if ($product->getTypeId() != 'configurable') {
                $childIds[] = $product->getId();
            }
        }

        $parentIdsOfChildIds = [];

        foreach ($childSkusOfParentSkus as $parentSku => $childSkus) {
            foreach ($childSkus as $childSku) {
                $childId = $configurableIdsOfSkus[$childSku];
                $parentIdsOfChildIds[$childId][] = $configurableIdsOfSkus[$parentSku];
            }
        }

        /**
         * Assert there are parent configurable products ids in result of getRelationsByChildren method
         * and they are related to child ids.
         */
        $result = $this->model->getRelationsByChildren($childIds);
        $sortedResult = $this->sortParentIdsOfChildIds($result);
        $sortedExpected = $this->sortParentIdsOfChildIds($parentIdsOfChildIds);

        $this->assertEquals($sortedExpected, $sortedResult);
    }

    /**
     * Sorts the "Parent Ids Of Child Ids" type of the array
     *
     * @param array $array
     * @return array
     */
    private function sortParentIdsOfChildIds(array $array): array
    {
        foreach ($array as &$parentIds) {
            sort($parentIds, SORT_NUMERIC);
        }

        ksort($array, SORT_NUMERIC);

        return $array;
    }
}
