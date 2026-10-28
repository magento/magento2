<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    use MockCreationTrait;

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
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);

        $this->context = $this->createMock(Context::class);
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->requestMock);

        $this->helper = $this->createMock(Helper::class);

        $this->currency = $this->createPartialMock(
            Currency::class,
            ['getAnyRate']
        );

        $this->currencyLocator = $this->createMock(DefaultLocator::class);

        $this->objectManagerHelper = new ObjectManager($this);

        $this->columnMock = $this->createPartialMockWithReflection(
            Column::class,
            ['getCurrencyCode']
        );

        $helper = $this->objectManagerHelper;

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
