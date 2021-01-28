<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Test\Unit\Render;

use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;

/**
 * Test class for \Magento\Framework\Pricing\Render\Amount
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AmountTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Amount
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrency;

    /**
     * @var RendererPool|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rendererPool;

    /**
     * @var \Magento\Framework\View\LayoutInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layout;

    /**
     * @var SaleableInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $amount;

    /**
     * @var PriceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceMock;

    protected function setUp(): void
    {
        $this->priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $data = [
            'default' => [
                'adjustments' => [
                    'base_price_test' => [
                        'tax' => [
                            'adjustment_render_class' => \Magento\Framework\View\Element\Template::class,
                            'adjustment_render_template' => 'template.phtml',
                        ],
                    ],
                ],
            ],
        ];

        $this->rendererPool = $this->getMockBuilder(\Magento\Framework\Pricing\Render\RendererPool::class)
            ->setConstructorArgs(['data' => $data])
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->amount = $this->getMockForAbstractClass(\Magento\Framework\Pricing\Amount\AmountInterface::class);
        $this->saleableItemMock = $this->getMockForAbstractClass(\Magento\Framework\Pricing\SaleableInterface::class);
        $this->priceMock = $this->getMockForAbstractClass(\Magento\Framework\Pricing\Price\PriceInterface::class);

        $eventManager = $this->createMock(\Magento\Framework\Event\Test\Unit\ManagerStub::class);
        $scopeConfigMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $context->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($scopeConfigMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Framework\Pricing\Render\Amount::class,
            [
                'context' => $context,
                'priceCurrency' => $this->priceCurrency,
                'rendererPool' => $this->rendererPool,
                'amount' => $this->amount,
                'saleableItem' => $this->saleableItemMock,
                'price' => $this->priceMock
            ]
        );
    }

    public function testFormatCurrency()
    {
        $amount = '100';
        $includeContainer = true;
        $precision = \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION;

        $result = '100.0 grn';

        $this->priceCurrency->expects($this->once())
            ->method('format')
            ->with($amount, $includeContainer, $precision)
            ->willReturn($result);

        $this->assertEquals($result, $this->model->formatCurrency($amount, $includeContainer, $precision));
    }

    public function testGetDisplayCurrencySymbol()
    {
        $currencySymbol = '$';
        $this->priceCurrency->expects($this->once())
            ->method('getCurrencySymbol')
            ->willReturn($currencySymbol);
        $this->assertEquals($currencySymbol, $this->model->getDisplayCurrencySymbol());
    }

    /**
     * Test case for getAdjustmentRenders method through toHtml()
     *
     * @param bool $hasSkipAdjustments
     * @param bool|null $skipAdjustments
     * @param string $expected
     * @dataProvider dataProviderToHtmlSkipAdjustments
     */
    public function testToHtmlSkipAdjustments($hasSkipAdjustments, $skipAdjustments, $expected)
    {
        if ($hasSkipAdjustments) {
            $this->model->setData('skip_adjustments', $skipAdjustments);
            $expectedData = [
                'key1' => 'data1',
                'css_classes' => 'class1 class2',
                'module_name' => null,
                'adjustment_css_classes' => 'class1 class2 render1 render2',
                'skip_adjustments' => $skipAdjustments
            ];
        } else {
            $expectedData = [
                'key1'                   => 'data1',
                'css_classes'            => 'class1 class2',
                'module_name'            => null,
                'adjustment_css_classes' => 'class1 class2 render1 render2',
            ];
        }

        $this->model->setData('key1', 'data1');
        $this->model->setData('css_classes', 'class1 class2');

        $adjustmentRender1 = $this->getAdjustmentRenderMock($expectedData, 'html');
        $adjustmentRender2 = $this->getAdjustmentRenderMock($expectedData);
        $adjustmentRenders = ['render1' => $adjustmentRender1, 'render2' => $adjustmentRender2];
        $this->rendererPool->expects($this->once())
            ->method('getAdjustmentRenders')
            ->willReturn($adjustmentRenders);

        $this->model->toHtml();
        $this->assertEquals($expected, $this->model->getAdjustmentsHtml());
    }

    /**
     * @return array
     */
    public function dataProviderToHtmlSkipAdjustments()
    {
        return [
            [false, null, 'html'],
            [false, null, 'html'],
            [true, false, 'html'],
            [true, true, ''],
        ];
    }

    /**
     * Test case for getAdjustmentRenders method through toHtml()
     */
    public function testToHtmlGetAdjustmentRenders()
    {
        $data = ['key1' => 'data1', 'css_classes' => 'class1 class2'];
        $expectedData = [
            'key1' => 'data1',
            'css_classes' => 'class1 class2',
            'module_name' => null,
            'adjustment_css_classes' => 'class1 class2 render1 render2',
        ];

        $this->model->setData($data);

        $adjustmentRender1 = $this->getAdjustmentRenderMock($expectedData);
        $adjustmentRender2 = $this->getAdjustmentRenderMock($expectedData);
        $adjustmentRenders = ['render1' => $adjustmentRender1, 'render2' => $adjustmentRender2];
        $this->rendererPool->expects($this->once())
            ->method('getAdjustmentRenders')
            ->willReturn($adjustmentRenders);
        $this->amount->expects($this->atLeastOnce())
            ->method('getAdjustmentAmount')
            ->willReturn(true);

        $this->model->toHtml();
    }

    public function testGetDisplayValueExiting()
    {
        $displayValue = 5.99;
        $this->model->setDisplayValue($displayValue);
        $this->assertEquals($displayValue, $this->model->getDisplayValue());
    }

    public function testGetDisplayValue()
    {
        $amountValue = 100.99;
        $this->amount->expects($this->once())
            ->method('getValue')
            ->willReturn($amountValue);
        $this->assertEquals($amountValue, $this->model->getDisplayValue());
    }

    public function testGetAmount()
    {
        $this->assertEquals($this->amount, $this->model->getAmount());
    }

    public function testGetSealableItem()
    {
        $this->assertEquals($this->saleableItemMock, $this->model->getSaleableItem());
    }

    public function testGetPrice()
    {
        $this->assertEquals($this->priceMock, $this->model->getPrice());
    }

    public function testAdjustmentsHtml()
    {
        $adjustmentHtml1 = 'adjustment_1_html';
        $adjustmentHtml2 = 'adjustment_2_html';
        $data = ['key1' => 'data1', 'css_classes' => 'class1 class2'];
        $expectedData = [
            'key1' => 'data1',
            'css_classes' => 'class1 class2',
            'module_name' => null,
            'adjustment_css_classes' => 'class1 class2 render1 render2',
        ];

        $this->model->setData($data);

        $this->assertFalse($this->model->hasAdjustmentsHtml());

        $adjustmentRender1 = $this->getAdjustmentRenderMock($expectedData, $adjustmentHtml1, 'adjustment_code1');
        $adjustmentRender2 = $this->getAdjustmentRenderMock($expectedData, $adjustmentHtml2, 'adjustment_code2');
        $adjustmentRenders = ['render1' => $adjustmentRender1, 'render2' => $adjustmentRender2];
        $this->rendererPool->expects($this->once())
            ->method('getAdjustmentRenders')
            ->willReturn($adjustmentRenders);
        $this->amount->expects($this->atLeastOnce())
            ->method('getAdjustmentAmount')
            ->willReturn(true);

        $this->model->toHtml();

        $this->assertTrue($this->model->hasAdjustmentsHtml());

        $this->assertEquals($adjustmentHtml1 . $adjustmentHtml2, $this->model->getAdjustmentsHtml());
    }

    /**
     * @param array $data
     * @param string $html
     * @param string $code
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getAdjustmentRenderMock($data = [], $html = '', $code = 'adjustment_code')
    {
        $adjustmentRender = $this->getMockForAbstractClass(
            \Magento\Framework\Pricing\Render\AdjustmentRenderInterface::class
        );
        $adjustmentRender->expects($this->once())
            ->method('render')
            ->with($this->model, $data)
            ->willReturn($html);
        $adjustmentRender->expects($this->any())
            ->method('getAdjustmentCode')
            ->willReturn($code);
        return $adjustmentRender;
    }
}
