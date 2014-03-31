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
namespace Magento\Catalog\Block\Product;

class ListProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $block;

    /**
     * @var \Magento\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->registryMock = $this->getMock('Magento\Registry', array(), array(), '', false);
        $this->layerMock = $this->getMock('Magento\Catalog\Model\Layer', array(), array(), '', false);
        $this->block = $objectManager->getObject(
            'Magento\Catalog\Block\Product\ListProduct',
            array('registry' => $this->registryMock, 'catalogLayer' => $this->layerMock)
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTag = 'catalog_product_1';
        $categoryTag = 'catalog_category_1';

        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getIdentities', '__wakeup'),
            array(),
            '',
            false
        );
        $product->expects($this->once())->method('getIdentities')->will($this->returnValue(array($productTag)));

        $itemsCollection = new \ReflectionProperty('Magento\Catalog\Block\Product\ListProduct', '_productCollection');
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, array($product));

        $currentCategory = $this->getMock('Magento\Catalog\Model\Category', array(), array(), '', false);
        $currentCategory->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue(array($categoryTag)));

        $this->layerMock->expects($this->once())
            ->method('getCurrentCategory')
            ->will($this->returnValue($currentCategory));

        $this->assertEquals(
            array($categoryTag, $productTag),
            $this->block->getIdentities()
        );
    }
}
