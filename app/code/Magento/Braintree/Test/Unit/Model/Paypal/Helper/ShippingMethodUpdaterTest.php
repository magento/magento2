<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Model\Paypal\Helper;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Paypal\Helper\ShippingMethodUpdater;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test shipping method updater
 */
class ShippingMethodUpdaterTest extends TestCase
{
    const TEST_SHIPPING_METHOD = 'test-shipping-method';

    const TEST_EMAIL = 'test@test.loc';

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var Address|MockObject
     */
    private $shippingAddressMock;

    /**
     * @var Address|MockObject
     */
    private $billingAddressMock;

    /**
     * @var ShippingMethodUpdater
     */
    private $shippingMethodUpdater;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->shippingAddressMock = $this->getMockBuilder(Address::class)
            ->setMethods(
                [
                    'setShouldIgnoreValidation',
                    'getShippingMethod',
                    'setShippingMethod',
                    'setCollectShippingRates'
                ]
            )->disableOriginalConstructor()
            ->getMock();

        $this->shippingMethodUpdater = new ShippingMethodUpdater(
            $this->configMock,
            $this->quoteRepositoryMock
        );
    }

    /**
     * Test execute exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "shippingMethod" field does not exists.
     */
    public function testExecuteException(): void
    {
        $quoteMock = $this->getQuoteMock();

        $this->shippingMethodUpdater->execute('', $quoteMock);
    }

    /**
     * Test execute
     */
    public function testExecute(): void
    {
        $quoteMock = $this->getQuoteMock();

        $quoteMock->expects(self::exactly(2))
            ->method('getIsVirtual')
            ->willReturn(false);

        $quoteMock->expects(self::exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->shippingAddressMock->expects(self::once())
            ->method('getShippingMethod')
            ->willReturn(self::TEST_SHIPPING_METHOD . '-bad');

        $this->disabledQuoteAddressValidationStep($quoteMock);

        $this->shippingAddressMock->expects(self::once())
            ->method('setShippingMethod')
            ->willReturn(self::TEST_SHIPPING_METHOD);
        $this->shippingAddressMock->expects(self::once())
            ->method('setCollectShippingRates')
            ->willReturn(true);

        $quoteMock->expects(self::once())
            ->method('collectTotals');

        $this->quoteRepositoryMock->expects(self::once())
            ->method('save')
            ->with($quoteMock);

        $this->shippingMethodUpdater->execute(self::TEST_SHIPPING_METHOD, $quoteMock);
    }

    /**
     * Disable quote address validation
     *
     * @param MockObject $quoteMock
     *
     * @return void
     */
    private function disabledQuoteAddressValidationStep(MockObject $quoteMock): void
    {
        $billingAddressMock = $this->getBillingAddressMock($quoteMock);

        $billingAddressMock->expects(self::once())
            ->method('setShouldIgnoreValidation')
            ->with(true)
            ->willReturnSelf();

        $this->shippingAddressMock->expects(self::once())
            ->method('setShouldIgnoreValidation')
            ->with(true)
            ->willReturnSelf();

        $billingAddressMock->expects(self::at(1))
            ->method('getEmail')
            ->willReturn(self::TEST_EMAIL);

        $billingAddressMock->expects(self::never())
            ->method('setSameAsBilling');
    }

    /**
     * Get billing address mock object
     *
     * @param MockObject $quoteMock
     * @return Address|MockObject
     */
    private function getBillingAddressMock(MockObject $quoteMock): MockObject
    {
        if (!isset($this->billingAddressMock)) {
            $this->billingAddressMock = $this->getMockBuilder(Address::class)
                ->setMethods(['setShouldIgnoreValidation', 'getEmail', 'setSameAsBilling'])
                ->disableOriginalConstructor()
                ->getMock();
        }

        $quoteMock->expects(self::any())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressMock);

        return $this->billingAddressMock;
    }

    /**
     * Get quote mock object
     *
     * @return Quote|MockObject
     */
    private function getQuoteMock(): MockObject
    {
        return $this->getMockBuilder(Quote::class)
            ->setMethods(
                [
                    'collectTotals',
                    'getBillingAddress',
                    'getShippingAddress',
                    'getIsVirtual',
                    'getExtensionAttributes'
                ]
            )->disableOriginalConstructor()
            ->getMock();
    }
}
