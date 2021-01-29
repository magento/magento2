<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Unit\Helper;

/**
 * Multishipping data helper Test
 */
class DataTest extends \PHPUnit\Framework\TestCase
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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * Quote mock
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Quote\Model\Quote
     */
    protected $quoteMock;

    /**
     * Checkout session mock
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Checkout\Model\Session
     */
    protected $checkoutSessionMock;

    protected function setUp(): void
    {
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = $objectManager->getConstructArguments(\Magento\Multishipping\Helper\Data::class);
        $this->helper = $objectManager->getObject(\Magento\Multishipping\Helper\Data::class, $arguments);
        $this->checkoutSessionMock = $arguments['checkoutSession'];
        /** @var \Magento\Framework\App\Helper\Context $context */
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
            \Magento\Multishipping\Helper\Data::XML_PATH_CHECKOUT_MULTIPLE_MAXIMUM_QUANTITY
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
            \Magento\Multishipping\Helper\Data::XML_PATH_CHECKOUT_MULTIPLE_AVAILABLE
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
            \Magento\Multishipping\Helper\Data::XML_PATH_CHECKOUT_MULTIPLE_MAXIMUM_QUANTITY
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
    public function isMultishippingCheckoutAvailableDataProvider()
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
