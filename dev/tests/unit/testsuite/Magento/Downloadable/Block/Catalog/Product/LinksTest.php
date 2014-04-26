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

namespace Magento\Downloadable\Block\Catalog\Product;

/**
 * Tests Magento\Downloadable\Block\Catalog\Product\Links
 */
class LinksTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Downloadable\Block\Catalog\Product\Links */
    protected $linksBlock;

    /**
     * @var \Magento\Downloadable\Model\Link|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkMock;

    /**
     * @var \Magento\Downloadable\Pricing\Price\LinkPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkPriceMock;

    /**
     * @var \Magento\Framework\Pricing\Amount\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salableItemMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $contextMock = $this->getMock('Magento\Catalog\Block\Product\Context', [], [], '', false, false);

        $this->priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $this->linkPriceMock = $this->getMock('Magento\Downloadable\Pricing\Price\LinkPrice', [], [], '', false);
        $this->salableItemMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->amountMock = $this->getMock('Magento\Framework\Pricing\Amount\Base', [], [], '', false);
        $this->linkMock = $this->getMock('Magento\Downloadable\Model\Link', [], [], '', false);
        $this->layout = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $contextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));
        $data = [
            'product' => $this->salableItemMock
        ];

        $this->linksBlock = $objectManager->getObject(
            'Magento\Downloadable\Block\Catalog\Product\Links',
            [
                'context' => $contextMock,
                'data' => $data
            ]
        );
    }

    public function testGetLinkPrice()
    {
        $priceCode = 'link_price';
        $arguments = [];
        $expectedHtml = 'some html';
        $this->salableItemMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->with($this->equalTo($priceCode))
            ->will($this->returnValue($this->linkPriceMock));
        $this->linkPriceMock->expects($this->any())
            ->method('getLinkAmount')
            ->with($this->linkMock)
            ->will($this->returnValue($this->amountMock));

        $priceBoxMock = $this->getMock('Magento\Framework\Pricing\Render', ['renderAmount'], [], '', false, false);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($this->equalTo('product.price.render.default'))
            ->will($this->returnValue($priceBoxMock));

        $priceBoxMock->expects($this->once())
            ->method('renderAmount')
            ->with($this->amountMock, $this->linkPriceMock, $this->salableItemMock, $arguments)
            ->will($this->returnValue($expectedHtml));

        $result = $this->linksBlock->getLinkPrice($this->linkMock);
        $this->assertEquals($expectedHtml, $result);
    }
}
 