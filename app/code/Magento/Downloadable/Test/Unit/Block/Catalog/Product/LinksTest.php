<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Unit\Block\Catalog\Product;

use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Tests Magento\Downloadable\Block\Catalog\Product\Links
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinksTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\Json\EncoderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoder;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $contextMock = $this->createMock(\Magento\Catalog\Block\Product\Context::class);
        $contextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));
        $this->priceInfoMock = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->jsonEncoder = $this->createMock(\Magento\Framework\Json\EncoderInterface::class);

        $this->linksBlock = $objectManager->getObject(
            \Magento\Downloadable\Block\Catalog\Product\Links::class,
            [
                'context' => $contextMock,
                'encoder' => $this->jsonEncoder,
                'data' => [
                    'product' => $this->productMock,
                ]
            ]
        );
    }

    public function testGetLinkPrice()
    {
        $linkPriceMock = $this->createMock(\Magento\Downloadable\Pricing\Price\LinkPrice::class);
        $amountMock = $this->createMock(\Magento\Framework\Pricing\Amount\Base::class);
        $linkMock = $this->createMock(\Magento\Downloadable\Model\Link::class);

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

        $priceBoxMock = $this->createPartialMock(\Magento\Framework\Pricing\Render::class, ['renderAmount']);

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
        $linkPrice = 3.;
        $basePrice = 3.;
        $linkId = 42;

        $config = [
            'links' => [
                $linkId => [
                    'finalPrice' => $linkPrice,
                    'basePrice' => $basePrice
                ],
            ],
        ];

        $linkAmountMock = $this->createMock(\Magento\Framework\Pricing\Amount\AmountInterface::class);
        $linkAmountMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($linkPrice));
        $linkAmountMock->expects($this->once())
            ->method('getBaseAmount')
            ->will($this->returnValue($linkPrice));

        $typeInstanceMock = $this->createPartialMock(\Magento\Catalog\Model\Product\Type\Simple::class, ['getLinks']);
        $typeInstanceMock->expects($this->once())
            ->method('getLinks')
            ->will($this->returnValue([$this->getLinkMock($linkPrice, $linkId)]));
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $finalPriceMock = $this->createMock(\Magento\Catalog\Pricing\Price\FinalPrice::class);
        $finalPriceMock->expects($this->once())
            ->method('getCustomAmount')
            ->with($linkPrice)
            ->will($this->returnValue($linkAmountMock));

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->will($this->returnValue($finalPriceMock));

        $json = json_encode($config);
        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->with($config)
            ->will($this->returnValue($json));

        $encodedJsonConfig = $this->linksBlock->getJsonConfig();
        $this->assertEquals(json_encode($config), $encodedJsonConfig);
    }

    /**
     * @param $linkPrice
     * @param $linkId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLinkMock($linkPrice, $linkId)
    {
        $linkMock = $this->createPartialMock(\Magento\Downloadable\Model\Link::class, ['getPrice',
            'getId',
            '__wakeup']);
        $linkMock->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($linkPrice));
        $linkMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($linkId));

        return $linkMock;
    }
}
