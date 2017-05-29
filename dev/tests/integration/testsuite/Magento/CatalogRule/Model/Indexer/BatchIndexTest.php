<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/CatalogRule/_files/two_rules.php
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class BatchIndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule
     */
    protected $resourceRule;

    protected function setUp()
    {
        $this->resourceRule = Bootstrap::getObjectManager()->get(\Magento\CatalogRule\Model\ResourceModel\Rule::class);
        $this->product = Bootstrap::getObjectManager()->get(\Magento\Catalog\Model\Product::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(\Magento\Catalog\Model\ProductRepository::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @dataProvider dataProvider
     */
    public function testPriceForSmallBatch($batchCount, $price, $expectedPrice)
    {
        $productIds = $this->prepareProducts($price);

        /**
         * @var IndexBuilder $indexerBuilder
         */
        $indexerBuilder = Bootstrap::getObjectManager()->create(
            \Magento\CatalogRule\Model\Indexer\IndexBuilder::class,
            ['batchCount' => $batchCount]
        );

        $indexerBuilder->reindexFull();

        foreach ([0, 1] as $customerGroupId) {
            foreach ($productIds as $productId) {
                $this->assertEquals(
                    $expectedPrice,
                    $this->resourceRule->getRulePrice(new \DateTime(), 1, $customerGroupId, $productId)
                );
            }
        }
    }

    /**
     * @return array
     */
    protected function prepareProducts($price)
    {
        $this->product = $this->productRepository->get('simple');
        $productSecond = clone $this->product;
        $productSecond->setId(null)
            ->setUrlKey(null)
            ->setSku(uniqid($this->product->getSku() . '-'))
            ->setName(uniqid($this->product->getName() . '-'))
            ->setWebsiteIds([1]);
        $productSecond->save();
        $productSecond->setPrice($price)->save();
        $productThird = clone $this->product;
        $productThird->setId(null)
            ->setUrlKey(null)
            ->setSku(uniqid($this->product->getSku() . '-'))
            ->setName(uniqid($this->product->getName() . '-'))
            ->setWebsiteIds([1])
            ->save();
        $productThird->setPrice($price)->save();
        return [
            $productSecond->getEntityId(),
            $productThird->getEntityId(),
        ];
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [1, 20, 17],
            [3, 40, 36],
            [3, 60, 55],
            [5, 100, 93],
            [8, 200, 188],
            [10, 500, 473],
            [11, 760, 720],
        ];
    }
}
