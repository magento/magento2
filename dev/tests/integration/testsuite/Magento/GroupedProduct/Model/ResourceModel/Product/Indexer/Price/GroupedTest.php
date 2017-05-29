<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Test class for Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Price\Grouped
 */
class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    private $tierPriceFactory;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->productCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
        $this->indexerProcessor = Bootstrap::getObjectManager()->get(Processor::class);
        $this->tierPriceFactory = Bootstrap::getObjectManager()->get(ProductTierPriceInterfaceFactory::class);
    }

    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoAppIsolation enabled
     */
    public function testReindex()
    {
        $simpleProductPrice = 15;
        $virtualProductPrice = 5;
        /** @var \Magento\Catalog\Model\Product $simpleProduct */
        $simpleProduct = $this->productRepository->get('simple', true);
        /** @var \Magento\Catalog\Model\Product $virtualProduct */
        $virtualProduct = $this->productRepository->get('virtual-product', true);
        $simpleProduct->setData('price', $simpleProductPrice);
        $virtualProduct->setData('price', $virtualProductPrice);
        $this->productRepository->save($simpleProduct);
        $this->productRepository->save($virtualProduct);
        $this->indexerProcessor->reindexAll();
        /** @var ProductCollection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addPriceData()->addFieldToFilter(ProductInterface::SKU, 'grouped-product');
        /** @var \Magento\Catalog\Model\Product $item */
        $item = $collection->getFirstItem();
        $this->assertEquals($virtualProductPrice, $item->getData('min_price'));
        $this->assertEquals($simpleProductPrice, $item->getData('max_price'));
    }
}
