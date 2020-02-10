<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\TestFramework\Indexer\TestCase as IndexerTestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\ResourceModel\Product\Flat;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Custom Flat Attribute Test
 */
class CustomFlatAttributeTest extends IndexerTestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->processor = $this->objectManager->get(Processor::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Tests that custom product attribute will appear in flat table and can be updated in it.
     *
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute_in_flat.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testProductUpdateCustomAttribute(): void
    {
        $product = $this->productRepository->get('simple_with_custom_flat_attribute');
        $product->setCustomAttribute('flat_attribute', 'changed flat attribute');
        $this->productRepository->save($product);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        /** @var \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria */
        $searchCriteria = $searchCriteriaBuilder->addFilter('sku', 'simple_with_custom_flat_attribute')
            ->create();

        $items = $this->productRepository->getList($searchCriteria)
            ->getItems();
        $product = reset($items);
        $resourceModel = $product->getResourceCollection()
            ->getEntity();

        self::assertInstanceOf(
            Flat::class,
            $resourceModel,
            'Product should be received from flat resource'
        );

        self::assertEquals(
            'changed flat attribute',
            $product->getFlatAttribute(),
            'Product flat attribute should be able to change.'
        );
    }
}
