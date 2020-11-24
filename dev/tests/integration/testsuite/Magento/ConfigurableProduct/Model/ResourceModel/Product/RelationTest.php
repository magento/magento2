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
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testGetRelationsByChildren(): void
    {
        // Find configurable products options
        $productOptionSkus = ['simple_10', 'simple_20', 'simple_30', 'simple_40'];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', $productOptionSkus, 'in')
            ->create();
        $productOptions = $this->productRepository->getList($searchCriteria)
            ->getItems();

        $productOptionsIds = [];

        foreach ($productOptions as $productOption) {
            $productOptionsIds[] = $productOption->getId();
        }

        // Find configurable products
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', ['configurable', 'configurable_12345'], 'in')
            ->create();
        $configurableProducts = $this->productRepository->getList($searchCriteria)
            ->getItems();

        // Assert there are configurable products ids in result of getRelationsByChildren method.
        $result = $this->model->getRelationsByChildren($productOptionsIds);

        foreach ($configurableProducts as $configurableProduct) {
            $this->assertContains($configurableProduct->getId(), $result);
        }
    }
}
