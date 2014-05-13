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
namespace Magento\Catalog\Block\Product\Compare;

/**
 * Class ListCompareTest
 */
class ListCompareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ListCompare
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\LayoutInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    protected function setUp()
    {
        $this->layout = $this->getMock('Magento\Framework\View\Layout', ['getBlock'], [], '', false);

        $context = $this->getMock('Magento\Catalog\Block\Product\Context', ['getLayout'], [], '', false);
        $context->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(
            'Magento\Catalog\Block\Product\Compare\ListCompare',
            ['context' => $context]
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetProductPrice()
    {
        //Data
        $expectedResult = 'html';
        $blockName = 'product.price.render.default';
        $productId = 1;

        //Verification
        $product = $this->getMock('Magento\Catalog\Model\Product', ['getId', '__wakeup'], [], '', false);
        $product->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));

        $blockMock = $this->getMock('Magento\Framework\Pricing\Render', ['render'], [], '', false);
        $blockMock->expects($this->once())
            ->method('render')
            ->with(
                'final_price',
                $product,
                [
                    'price_id' => 'product-price-' . $productId . '-compare-list-top',
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            )
            ->will($this->returnValue($expectedResult));

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($blockName)
            ->will($this->returnValue($blockMock));

        $this->assertEquals($expectedResult, $this->block->getProductPrice($product, '-compare-list-top'));
    }
}
