<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Link\Product;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection::class
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_crosssell.php
     */
    public function testAddLinkAttributeToFilterWithResults()
    {
        $om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $link = $om->get(\Magento\Catalog\Model\Product\Link::class)->useCrossSellLinks();
        $this->collection->setLinkModel($link);
        $this->collection->addLinkAttributeToFilter('position', ['from' => 0, 'to' => 2]);
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple_with_cross');
        $this->collection->setProduct($product);
        $this->collection->load();
        $this->assertCount(1, $this->collection->getItems());
        foreach ($this->collection as $item) {
            $this->assertGreaterThan(0, $item->getPosition());
            $this->assertLessThan(2, $item->getPosition());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_crosssell.php
     */
    public function testAddLinkAttributeToFilterNoResults()
    {
        $om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $link = $om->get(\Magento\Catalog\Model\Product\Link::class)->useCrossSellLinks();
        $this->collection->setLinkModel($link);
        $this->collection->addLinkAttributeToFilter('position', ['from' => 2, 'to' => 3]);
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple_with_cross');
        $this->collection->setProduct($product);
        $this->collection->load();
        $this->assertCount(0, $this->collection->getItems());
    }
}
