<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Block\Catalog\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Downloadable\Block\Catalog\Product\Links;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Pricing\Price\LinkPrice;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\Render;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Downloadable\Block\Catalog\Product\Links
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinksTest extends TestCase
{
    /**
     * @var Links
     */
    protected $linksBlock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Base|MockObject
     */
    protected $priceInfoMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layout;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $jsonEncoder;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->layout = $this->createMock(Layout::class);
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($this->layout);
        $this->priceInfoMock = $this->createMock(Base::class);
        $this->productMock = $this->createMock(Product::class);
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);
        $this->jsonEncoder = $this->getMockForAbstractClass(EncoderInterface::class);

        $this->linksBlock = $objectManager->getObject(
            Links::class,
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
        $linkPriceMock = $this->createMock(LinkPrice::class);
        $amountMock = $this->createMock(\Magento\Framework\Pricing\Amount\Base::class);
        $linkMock = $this->createMock(Link::class);

        $priceCode = 'link_price';
        $arguments = [];
        $expectedHtml = 'some html';
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);
        $this->priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->with($priceCode)
            ->willReturn($linkPriceMock);
        $linkPriceMock->expects($this->any())
            ->method('getLinkAmount')
            ->with($linkMock)
            ->willReturn($amountMock);

        $priceBoxMock = $this->createPartialMock(Render::class, ['renderAmount']);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($priceBoxMock);

        $priceBoxMock->expects($this->once())
            ->method('renderAmount')
            ->with($amountMock, $linkPriceMock, $this->productMock, $arguments)
            ->willReturn($expectedHtml);

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

        $linkAmountMock = $this->getMockForAbstractClass(AmountInterface::class);
        $linkAmountMock->expects($this->once())
            ->method('getValue')
            ->willReturn($linkPrice);
        $linkAmountMock->expects($this->once())
            ->method('getBaseAmount')
            ->willReturn($linkPrice);

        $typeInstanceMock = $this->getMockBuilder(Simple::class)
            ->addMethods(['getLinks'])
            ->disableOriginalConstructor()
            ->getMock();
        $typeInstanceMock->expects($this->once())
            ->method('getLinks')
            ->willReturn([$this->getLinkMock($linkPrice, $linkId)]);
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstanceMock);

        $finalPriceMock = $this->createMock(FinalPrice::class);
        $finalPriceMock->expects($this->once())
            ->method('getCustomAmount')
            ->with($linkPrice)
            ->willReturn($linkAmountMock);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($finalPriceMock);

        $json = json_encode($config);
        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->with($config)
            ->willReturn($json);

        $encodedJsonConfig = $this->linksBlock->getJsonConfig();
        $this->assertEquals(json_encode($config), $encodedJsonConfig);
    }

    /**
     * @param $linkPrice
     * @param $linkId
     * @return MockObject
     */
    protected function getLinkMock($linkPrice, $linkId)
    {
        $linkMock = $this->createPartialMock(Link::class, ['getPrice',
            'getId',
            '__wakeup']);
        $linkMock->expects($this->any())
            ->method('getPrice')
            ->willReturn($linkPrice);
        $linkMock->expects($this->any())
            ->method('getId')
            ->willReturn($linkId);

        return $linkMock;
    }
}
