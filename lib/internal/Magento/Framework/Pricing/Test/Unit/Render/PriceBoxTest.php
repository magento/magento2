<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Render;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\Pricing\Render\PriceBox;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Framework\Pricing\Render\PriceBox
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceBoxTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PriceBox
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var RendererPool|MockObject
     */
    protected $rendererPool;

    /**
     * @var SaleableInterface|MockObject
     */
    protected $saleable;

    /**
     * @var PriceInterface|MockObject
     */
    protected $price;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->rendererPool = $this->getMockBuilder(RendererPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['createAmountRender'])
            ->getMock();

        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $cacheState = $this->getMockBuilder(StateInterface::class)
            ->getMockForAbstractClass();
        $this->context = $this->getMockBuilder(Context::class)
            ->setMethods(['getLayout', 'getEventManager', 'getScopeConfig', 'getCacheState'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getLayout')
            ->willReturn($layout);
        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($scopeConfigMock);
        $this->context->expects($this->any())
            ->method('getCacheState')
            ->willReturn($cacheState);

        $this->saleable = $this->getMockForAbstractClass(SaleableInterface::class);

        $this->price = $this->getMockForAbstractClass(PriceInterface::class);

        $this->model = $this->objectManager->getObject(
            PriceBox::class,
            [
                'context' => $this->context,
                'saleableItem' => $this->saleable,
                'price' => $this->price,
                'rendererPool' => $this->rendererPool
            ]
        );
    }

    /**
     * @param array $data
     * @param string $priceCode
     * @param array $cssClasses
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml($data, $priceCode, $cssClasses)
    {
        $this->price->expects($this->once())
            ->method('getPriceCode')
            ->willReturn($priceCode);

        $priceBox = $this->objectManager->getObject(
            PriceBox::class,
            [
                'context' => $this->context,
                'saleableItem' => $this->saleable,
                'price' => $this->price,
                'rendererPool' => $this->rendererPool,
                'data' => $data
            ]
        );
        $priceBox->toHtml();
        $this->assertEquals($cssClasses, $priceBox->getData('css_classes'));
    }

    /**
     * @return array
     */
    public function toHtmlDataProvider()
    {
        return [
            [
                'data' => [],
                'price_code' => 'test_price',
                'css_classes' => 'price-test_price',
            ],
            [
                'data' => ['css_classes' => 'some_css_class'],
                'price_code' => 'test_price',
                'css_classes' => 'some_css_class price-test_price'
            ]];
    }

    public function testGetSaleableItem()
    {
        $this->assertEquals($this->saleable, $this->model->getSaleableItem());
    }

    public function testGetPrice()
    {
        $this->assertEquals($this->price, $this->model->getPrice());
    }

    public function testGetPriceType()
    {
        $priceCode = 'test_price';

        $price = $this->getMockForAbstractClass(PriceInterface::class);

        $priceInfo = $this->createMock(Base::class);
        $priceInfo->expects($this->once())
            ->method('getPrice')
            ->with($priceCode)
            ->willReturn($price);

        $this->saleable->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);

        $this->assertEquals($price, $this->model->getPriceType($priceCode));
    }

    public function testRenderAmount()
    {
        $amount = $this->getMockForAbstractClass(AmountInterface::class);
        $arguments = [];
        $resultHtml = 'result_html';

        $amountRender = $this->getMockBuilder(Amount::class)
            ->disableOriginalConstructor()
            ->setMethods(['toHtml'])
            ->getMock();
        $amountRender->expects($this->once())
            ->method('toHtml')
            ->willReturn($resultHtml);

        $this->rendererPool->expects($this->once())
            ->method('createAmountRender')
            ->with($amount, $this->saleable, $this->price, $arguments)
            ->willReturn($amountRender);

        $this->assertEquals($resultHtml, $this->model->renderAmount($amount, $arguments));
    }

    public function testGetPriceIdHasDataPriceId()
    {
        $priceId = 'data_price_id';
        $this->model->setData('price_id', $priceId);
        $this->assertEquals($priceId, $this->model->getPriceId());
    }

    /**
     * @dataProvider getPriceIdProvider
     * @param string $prefix
     * @param string $suffix
     * @param string $defaultPrefix
     * @param string $defaultSuffix
     */
    public function testGetPriceId($prefix, $suffix, $defaultPrefix, $defaultSuffix)
    {
        $priceId = 'price_id';
        $this->saleable->expects($this->once())
            ->method('getId')
            ->willReturn($priceId);

        if (!empty($prefix)) {
            $this->model->setData('price_id_prefix', $prefix);
            $expectedPriceId = $prefix . $priceId;
        } else {
            $expectedPriceId = $defaultPrefix . $priceId;
        }
        if (!empty($suffix)) {
            $this->model->setData('price_id_suffix', $suffix);
            $expectedPriceId = $expectedPriceId . $suffix;
        } else {
            $expectedPriceId = $expectedPriceId . $defaultSuffix;
        }

        $this->assertEquals($expectedPriceId, $this->model->getPriceId($defaultPrefix, $defaultSuffix));
    }

    /**
     * @return array
     */
    public function getPriceIdProvider()
    {
        return [
            ['prefix', 'suffix', 'default_prefix', 'default_suffix'],
            ['prefix', 'suffix', 'default_prefix', ''],
            ['prefix', 'suffix', '', 'default_suffix'],
            ['prefix', '', 'default_prefix', 'default_suffix'],
            ['', 'suffix', 'default_prefix', 'default_suffix'],
            ['', '', 'default_prefix', 'default_suffix'],
            ['prefix', 'suffix', '', '']
        ];
    }

    public function testGetRendererPool()
    {
        $this->assertEquals($this->rendererPool, $this->model->getRendererPool());
    }
}
