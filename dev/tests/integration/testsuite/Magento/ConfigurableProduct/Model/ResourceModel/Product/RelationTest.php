<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Fixture\DataFixture;
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
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple_10'], 'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple_20'], 'p2'),
        DataFixture(ProductFixture::class, ['sku' => 'simple_30'], 'p3'),
        DataFixture(ProductFixture::class, ['sku' => 'simple_40'], 'p4'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['sku' => 'conf1','_options' => ['$attr$'],'_links' => ['$p1$','$p2$']],
            'conf1'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['sku' => 'conf2','_options' => ['$attr$'],'_links' => ['$p3$','$p4$']],
            'conf2'
        ),
    ]
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
