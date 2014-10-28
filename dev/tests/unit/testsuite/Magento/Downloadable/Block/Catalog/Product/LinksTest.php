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

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;

/**
 * Tests Magento\Downloadable\Block\Catalog\Product\Links
 */
class LinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Block\Catalog\Product\Links
     */
    protected $linksBlock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Core\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreHelper;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->layout = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $contextMock = $this->getMock('Magento\Catalog\Block\Product\Context', [], [], '', false, false);
        $contextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));
        $this->priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->coreHelper = $this->getMock('Magento\Core\Helper\Data', [], [], '', false);

        $this->linksBlock = $objectManager->getObject(
            'Magento\Downloadable\Block\Catalog\Product\Links',
            [
                'context' => $contextMock,
                'coreData' => $this->coreHelper,
                'data' => [
                    'product' => $this->productMock
                ]
            ]
        );
    }

    public function testGetLinkPrice()
    {
        $linkPriceMock = $this->getMock('Magento\Downloadable\Pricing\Price\LinkPrice', [], [], '', false);
        $amountMock = $this->getMock('Magento\Framework\Pricing\Amount\Base', [], [], '', false);
        $linkMock = $this->getMock('Magento\Downloadable\Model\Link', [], [], '', false);

        $priceCode = 'link_price';
        $arguments = [];
        $expectedHtml = 'some html';
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->with($this->equalTo($priceCode))
            ->will($this->returnValue($linkPriceMock));
        $linkPriceMock->expects($this->any())
            ->method('getLinkAmount')
            ->with($linkMock)
            ->will($this->returnValue($amountMock));

        $priceBoxMock = $this->getMock('Magento\Framework\Pricing\Render', ['renderAmount'], [], '', false, false);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($this->equalTo('product.price.render.default'))
            ->will($this->returnValue($priceBoxMock));

        $priceBoxMock->expects($this->once())
            ->method('renderAmount')
            ->with($amountMock, $linkPriceMock, $this->productMock, $arguments)
            ->will($this->returnValue($expectedHtml));

        $result = $this->linksBlock->getLinkPrice($linkMock);
        $this->assertEquals($expectedHtml, $result);
    }

    public function testGetJsonConfig()
    {
        $regularPrice = 11.;
        $finalPrice = 10.;
        $price = 5.;
        $oldPrice = 4.;

        $linkPrice = 3.;
        $linkIncludeTaxPrice = 4.;
        $linkExcludeTaxPrice = 3.;

        $linkId = 42;

        $config = [
            'price' => $price,
            'oldPrice' => $oldPrice,
            'links' => [
                $linkId => [
                    'price' => $linkPrice,
                    'oldPrice' => $linkPrice,
                    'inclTaxPrice' => $linkIncludeTaxPrice,
                    'exclTaxPrice' => $linkExcludeTaxPrice
                ]
            ]
        ];

        $linkAmountMock = $this->getMock('Magento\Framework\Pricing\Amount\Base', [], [], '', false);
        $linkAmountMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($linkIncludeTaxPrice));
        $linkAmountMock->expects($this->once())
            ->method('getBaseAmount')
            ->will($this->returnValue($linkExcludeTaxPrice));

        $amountMock = $this->getMock('Magento\Framework\Pricing\Amount\Base', [], [], '', false);
        $amountMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($finalPrice));

        $typeInstanceMock = $this->getMock('Magento\Catalog\Model\Product\Type\Simple', ['getLinks'], [], '', false);
        $typeInstanceMock->expects($this->once())
            ->method('getLinks')
            ->will($this->returnValue([$this->getLinkMock($linkPrice, $linkId)]));
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $regularPriceMock = $this->getMock('Magento\Catalog\Pricing\Price\RegularPrice', [], [], '', false);
        $regularPriceMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($regularPrice));

        $finalPriceMock = $this->getMock('Magento\Catalog\Pricing\Price\FinalPrice', [], [], '', false);
        $finalPriceMock->expects($this->at(0))
            ->method('getAmount')
            ->will($this->returnValue($amountMock));
        $finalPriceMock->expects($this->at(1))
            ->method('getCustomAmount')
            ->with($linkPrice)
            ->will($this->returnValue($linkAmountMock));

        $this->coreHelper->expects($this->at(0))
            ->method('currency')
            ->with($finalPrice, false, false)
            ->will($this->returnValue($price));
        $this->coreHelper->expects($this->at(1))
            ->method('currency')
            ->with($regularPrice, false, false)
            ->will($this->returnValue($oldPrice));
        $this->coreHelper->expects($this->at(2))
            ->method('currency')
            ->with($linkPrice, false, false)
            ->will($this->returnValue($linkPrice));
        $this->coreHelper->expects($this->at(3))
            ->method('currency')
            ->with($linkIncludeTaxPrice, false, false)
            ->will($this->returnValue($linkIncludeTaxPrice));
        $this->coreHelper->expects($this->at(4))
            ->method('currency')
            ->with($linkExcludeTaxPrice, false, false)
            ->will($this->returnValue($linkExcludeTaxPrice));

        $this->priceInfoMock->expects($this->at(0))
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->will($this->returnValue($finalPriceMock));
        $this->priceInfoMock->expects($this->at(1))
            ->method('getPrice')
            ->with(RegularPrice::PRICE_CODE)
            ->will($this->returnValue($regularPriceMock));
        $this->priceInfoMock->expects($this->at(2))
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->will($this->returnValue($finalPriceMock));

        $this->assertEquals(json_encode($config), $this->linksBlock->getJsonConfig());
    }

    protected function getLinkMock($linkPrice, $linkId)
    {
        $linkMock = $this->getMock('Magento\Downloadable\Model\Link', ['getPrice', 'getId', '__wakeup'], [], '', false);
        $linkMock->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($linkPrice));
        $linkMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($linkId));

        return $linkMock;
    }
}
 