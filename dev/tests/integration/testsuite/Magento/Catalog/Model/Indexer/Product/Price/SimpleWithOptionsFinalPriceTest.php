<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product\Price;

class SimpleWithOptionsFinalPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productCollectionFactory = $this->objectManager->create(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
     */
    public function testFinalPrice()
    {
        $this->markTestSkipped('MAGETWO-64406');

        $product = $this->productRepository->get('simple', false, null, true);

        /** @var \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer */
        $priceIndexer = $this->objectManager->create(\Magento\Catalog\Model\Indexer\Product\Price\Processor::class);
        $priceIndexer->reindexRow($product->getId());

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addIdFilter([$product->getId()]);
        $productCollection->addPriceData();
        $productCollection->load();
        $indexPriceInfo = $productCollection->getFirstItem();

        $this->assertEquals(395, $indexPriceInfo->getMaxPrice());
        $this->assertEquals(50, $indexPriceInfo->getMinimalPrice());
    }
}
