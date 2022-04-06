<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Column\Filter\Price;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Helper;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\Currency\DefaultLocator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Helper|MockObject
     */
    private $helper;

    /**
     * @var Currency|MockObject
     */
    private $currency;

    /**
     * @var DefaultLocator|MockObject
     */
    private $currencyLocator;

    /**
     * @var Column|MockObject
     */
    private $columnMock;

    /**
     * @var Price
     */
    private $blockPrice;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $this->context = $this->createMock(Context::class);
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->requestMock);

        $this->helper = $this->createMock(Helper::class);

        $this->currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAnyRate'])
            ->getMock();

        $this->currencyLocator = $this->createMock(DefaultLocator::class);

        $this->columnMock = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCurrencyCode'])
            ->getMock();

        $helper = new ObjectManager($this);

        $this->blockPrice = $helper->getObject(Price::class, [
            'context'         => $this->context,
            'resourceHelper'  => $this->helper,
            'currencyModel'   => $this->currency,
            'currencyLocator' => $this->currencyLocator
        ]);
        $this->blockPrice->setColumn($this->columnMock);
    }

    /**
     * @return void
     */
    public function testGetCondition(): void
    {
        $this->currencyLocator->expects(
            $this->any()
        )->method(
            'getDefaultCurrency'
        )->with(
            $this->requestMock
        )->willReturn(
            'defaultCurrency'
        );

        $this->currency
            ->method('getAnyRate')
            ->with('defaultCurrency')
            ->willReturn(1.0);

        $testValue = [
            'value' => [
                'from' => '1234a'
            ]
        ];

        $this->blockPrice->addData($testValue);
        $this->assertEquals(['from' => 1234], $this->blockPrice->getCondition());
    }
}
