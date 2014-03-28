<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
