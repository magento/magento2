<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Block\Customer;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Block\Customer\Sidebar
     */
    protected $block;

    /**
     * @var \Magento\Wishlist\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistHelper;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->wishlistHelper = $this->getMock('Magento\Wishlist\Helper\Data', ['getItemCount'], [], '', false);
        $this->block = $objectManager->getObject(
            'Magento\Wishlist\Block\Customer\Sidebar',
            ['wishlistHelper' => $this->wishlistHelper]
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentitiesItemsPresent()
    {
        $productTags = ['catalog_product_1'];

        $this->wishlistHelper->expects($this->once())->method('getItemCount')->will($this->returnValue(5));

        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->once())->method('getIdentities')->will($this->returnValue($productTags));

        $item = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Item',
            ['getProduct', '__wakeup'],
            [],
            '',
            false
        );
        $item->expects($this->once())->method('getProduct')->will($this->returnValue($product));

        $collection = new \ReflectionProperty('Magento\Wishlist\Block\Customer\Sidebar', '_collection');
        $collection->setAccessible(true);
        $collection->setValue($this->block, [$item]);

        $this->assertEquals($productTags, $this->block->getIdentities());
    }

    public function testGetIdentitiesNoItems()
    {
        $productTags = [];

        $this->wishlistHelper->expects($this->once())->method('getItemCount')->will($this->returnValue(0));

        $this->assertEquals($productTags, $this->block->getIdentities());
    }
}
