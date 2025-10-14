<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Helper;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Multishipping\Helper\Data;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Multishipping data helper Test
 */
class DataTest extends TestCase
{
    /**
     * Multishipping data helper
     *
     * @var \Magento\Multishipping\Helper\Data
     */
    protected $helper;

    /**
     * Core store config mock
     *
     * @var MockObject|ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * Quote mock
     *
     * @var MockObject|\Magento\Quote\Model\Quote
     */
    protected $quoteMock;

    /**
     * Checkout session mock
     *
     * @var MockObject|Session
     */
    protected $checkoutSessionMock;

    protected function setUp(): void
    {
        $this->quoteMock = $this->createMock(Quote::class);

        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments(Data::class);
        $this->helper = $objectManager->getObject(Data::class, $arguments);
        $this->checkoutSessionMock = $arguments['checkoutSession'];
        /** @var Context $context */
        $context = $arguments['context'];
        $this->scopeConfigMock = $context->getScopeConfig();
    }

    public function testGetMaximumQty()
    {
        $maximumQty = 10;
        $this->scopeConfigMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            Data::XML_PATH_CHECKOUT_MULTIPLE_MAXIMUM_QUANTITY
        )->willReturn(
            $maximumQty
        );

        $this->assertEquals($maximumQty, $this->helper->getMaximumQty());
    }

    /**
     * @param bool $result
     * @param bool $quoteHasItems
     * @param bool $isMultiShipping
     * @param bool $hasItemsWithDecimalQty
     * @param bool $validateMinimumAmount
     * @param int $itemsSummaryQty
     * @param int $itemVirtualQty
     * @param int $maximumQty
     * @dataProvider isMultishippingCheckoutAvailableDataProvider
     */
    public function testIsMultishippingCheckoutAvailable(
        $result,
        $quoteHasItems,
        $isMultiShipping,
        $hasItemsWithDecimalQty,
        $validateMinimumAmount,
        $itemsSummaryQty,
        $itemVirtualQty,
        $maximumQty
    ) {
        $this->scopeConfigMock->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            Data::XML_PATH_CHECKOUT_MULTIPLE_AVAILABLE
        )->willReturn(
            $isMultiShipping
        );
        $this->checkoutSessionMock->expects(
            $this->once()
        )->method(
            'getQuote'
        )->willReturn(
            $this->quoteMock
        );
        $this->quoteMock->expects($this->once())->method('hasItems')->willReturn($quoteHasItems);

        $this->quoteMock->expects(
            $this->any()
        )->method(
            'hasItemsWithDecimalQty'
        )->willReturn(
            $hasItemsWithDecimalQty
        );
        $this->quoteMock->expects(
            $this->any()
        )->method(
            'validateMinimumAmount'
        )->with(
            true
        )->willReturn(
            $validateMinimumAmount
        );
        $this->quoteMock->expects(
            $this->any()
        )->method(
            'getItemsSummaryQty'
        )->willReturn(
            $itemsSummaryQty
        );
        $this->quoteMock->expects(
            $this->any()
        )->method(
            'getItemVirtualQty'
        )->willReturn(
            $itemVirtualQty
        );
        $this->scopeConfigMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            Data::XML_PATH_CHECKOUT_MULTIPLE_MAXIMUM_QUANTITY
        )->willReturn(
            $maximumQty
        );

        $this->assertEquals($result, $this->helper->isMultishippingCheckoutAvailable());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function isMultishippingCheckoutAvailableDataProvider()
    {
        return [
            [true, false, true, null, null, null, null, null],
            [false, false, false, null, null, null, null, null],
            [false, true, true, true, null, null, null, null],
            [false, true, true, false, false, null, null, null],
            [true, true, true, false, true, 2, 1, 3],
            [false, true, true, false, true, 1, 2, null],
            [false, true, true, false, true, 2, 1, 1],
        ];
    }
}
