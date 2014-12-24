<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Resource\Product\Link\Product;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $collection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Resource\Product\Link\Product\Collection'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_crosssell.php
     */
    public function testAddLinkAttributeToFilterWithResults()
    {
        $om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $link = $om->get('Magento\Catalog\Model\Product\Link')->useCrossSellLinks();
        $this->collection->setLinkModel($link);
        $this->collection->addLinkAttributeToFilter('position', ['from' => 0, 'to' => 2]);
        $product = $om->get('Magento\Catalog\Model\Product')->load(2);
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
        $link = $om->get('Magento\Catalog\Model\Product\Link')->useCrossSellLinks();
        $this->collection->setLinkModel($link);
        $this->collection->addLinkAttributeToFilter('position', ['from' => 2, 'to' => 3]);
        $product = $om->get('Magento\Catalog\Model\Product')->load(2);
        $this->collection->setProduct($product);
        $this->collection->load();
        $this->assertCount(0, $this->collection->getItems());
    }
}
