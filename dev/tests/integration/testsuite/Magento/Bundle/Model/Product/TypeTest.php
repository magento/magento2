<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * Test class for \Magento\Bundle\Model\Product\Type (bundle product type)
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Full reindex
     *
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connectionMock;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
        $indexerRegistry = $this->objectManager->create('\Magento\Framework\Indexer\IndexerRegistry');
        $this->indexer =  $indexerRegistry->get('catalogsearch_fulltext');

        $this->resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->connectionMock = $this->resource->getConnection();
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @covers \Magento\Indexer\Model\Indexer::reindexAll
     * @covers \Magento\Bundle\Model\Product\Type::getSearchableData
     */
    public function testPrepareProductIndexForBundleProduct()
    {
        $this->indexer->reindexAll();

        $select = $this->connectionMock->select()->from($this->resource->getTableName('catalogsearch_fulltext_scope1'))
            ->where('`data_index` LIKE ?', '%' . 'Bundle Product Items' . '%');

        $result = $this->connectionMock->fetchAll($select);
        $this->assertCount(1, $result);
    }

    /**
     * Test that having valid buyRequest it can be successfully prepared fro shopping cart
     *
     * @magentoDataFixture Magento/Bundle/_files/product_with_multiple_options.php
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testPrepareForCart()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('bundle-product');

        /** @var \Magento\Bundle\Api\ProductOptionRepositoryInterface $optionsRepository */
        $optionsRepository = $this->objectManager->create(\Magento\Bundle\Api\ProductOptionRepositoryInterface::class);
        $options = $optionsRepository->getList($product->getSku());

        $data = [
            'id' => '10',
            'product' => '3',
            'selected_configurable_option' => '',
            'related_product' => '',
            'bundle_option' => [],
            'bundle_option_qty' => [],
            'qty' => '1',
            'options' => [],
            'reset_count' => true,
        ];

        foreach ($options as $option) {
            /** @var \Magento\Bundle\Api\Data\LinkInterface $link */
            $link = current($option->getProductLinks());
            $data['bundle_option'][$option->getOptionId()] = $link->getId();
            $data['bundle_option_qty'][$option->getOptionId()] = 1;
        }

        $request =  $this->objectManager->create(\Magento\Framework\DataObject::class, ['data' => $data]);
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

        $result = $typeInstance->prepareForCart($request, $product);
        $this->assertEquals(count($data['bundle_option']) + 1, count($result), 'Incorrect product count');
    }

    /**
     * Test that having invalid selection option in buyRequest
     * prepareForCart method will return meaningful error message
     *
     * @magentoDataFixture Magento/Bundle/_files/product_with_multiple_options.php
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testPrepareForCartWithUnavailableOption()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('bundle-product');

        /** @var \Magento\Bundle\Api\ProductOptionRepositoryInterface $optionsRepository */
        $optionsRepository = $this->objectManager->create(\Magento\Bundle\Api\ProductOptionRepositoryInterface::class);
        $options = $optionsRepository->getList($product->getSku());

        $data = [
            'id' => '10',
            'product' => '3',
            'selected_configurable_option' => '',
            'related_product' => '',
            'bundle_option' => [],
            'bundle_option_qty' => [],
            'qty' => '1',
            'options' => [],
            'reset_count' => true,
        ];

        $option = null;
        foreach ($options as $option) {
            /** @var \Magento\Bundle\Api\Data\LinkInterface $link */
            $link = current($option->getProductLinks());
            $data['bundle_option'][$option->getOptionId()] = $link->getId();
            $data['bundle_option_qty'][$option->getOptionId()] = 1;
        }

        /** Set latest option selection to unavailable option */
        $data['bundle_option'][$option->getOptionId()] = 300;

        $buyRequest =  $this->objectManager->create(\Magento\Framework\DataObject::class, ['data' => $data]);
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

        $result = $typeInstance->prepareForCart($buyRequest, $product);
        $this->assertEquals('The options you selected are not available.', $result);
    }
}
