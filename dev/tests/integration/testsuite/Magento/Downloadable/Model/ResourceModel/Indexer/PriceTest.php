<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\ResourceModel\Indexer;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    private $indexer;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollectionFactory;

    protected function setUp(): void
    {
        $this->indexer = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\Indexer\Product\Price\Processor::class
        );
        $this->productCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(\Magento\Catalog\Model\ProductRepository::class);
    }

    /**
     * Steps:
     * 1. Add custom tier prices to the product from fixture.
     * 2. Run reindexing.
     * 3. Load the product again and check all the prices.
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     */
    public function testReindexEntity()
    {
        $specialPrice = 7.90;
        $product = $this->productRepository->get('downloadable-product');
        $tierData = [
            ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 11, 'price' => 8.20],
            ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 21, 'price' => 7.55],
        ];
        $product->setData('tier_price', $tierData);
        $product->setData('special_price', $specialPrice);
        $this->productRepository->save($product);

        $this->indexer->reindexAll();

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addPriceData()->addFieldToFilter('sku', 'downloadable-product');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $collection->getFirstItem();

        $this->assertEquals(10, $product->getPrice(), 'Wrong downloadable product price');
        $this->assertEquals($specialPrice, $product->getMinimalPrice());

        $resultTiers = $product->getTierPrices();
        $this->assertIsArray($resultTiers, 'Tiers not found');
        $this->assertEquals(count($tierData), count($resultTiers), 'Incorrect number of result tiers');

        for ($i = 0; $i < count($tierData); $i++) {
            $this->assertEquals($tierData[$i]['price_qty'], $resultTiers[$i]->getQty(), 'Wrong tier price quantity');
            $this->assertEquals($tierData[$i]['price'], $resultTiers[$i]->getValue(), 'Wrong tier price value');
        }
    }
}
