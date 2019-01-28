<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Paypal\Helper;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Paypal\Helper\ShippingMethodUpdater;

/**
 * Class ShippingMethodUpdaterTest
 *
 * @see \Magento\Braintree\Model\Paypal\Helper\ShippingMethodUpdater
 */
class ShippingMethodUpdaterTest extends \PHPUnit\Framework\TestCase
{
    const TEST_SHIPPING_METHOD = 'test-shipping-method';

    const TEST_EMAIL = 'test@test.loc';

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAddressMock;

    /**
     * @var ShippingMethodUpdater
     */
    private $shippingMethodUpdater;

    protected function setUp()
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "shippingMethod" field does not exists.
     */
    public function testExecuteException()
    {
        $quoteMock = $this->getQuoteMock();

        $this->shippingMethodUpdater->execute('', $quoteMock);
    }

    public function testExecute()
    {
        $quoteMock = $this->getQuoteMock();

        $quoteMock->expects($this->exactly(2))
            ->method('getIsVirtual')
            ->willReturn(false);

        $quoteMock->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingMethod')
            ->willReturn(self::TEST_SHIPPING_METHOD . '-bad');

        $this->disabledQuoteAddressValidationStep($quoteMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('setShippingMethod')
            ->willReturn(self::TEST_SHIPPING_METHOD);
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->willReturn(true);

        $quoteMock->expects($this->once())
            ->method('collectTotals');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock);

        $this->shippingMethodUpdater->execute(self::TEST_SHIPPING_METHOD, $quoteMock);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $quoteMock
     */
    private function disabledQuoteAddressValidationStep(\PHPUnit_Framework_MockObject_MockObject $quoteMock)
    {
        $billingAddressMock = $this->getBillingAddressMock($quoteMock);

        $billingAddressMock->expects($this->once())
            ->method('setShouldIgnoreValidation')
            ->with(true)
            ->willReturnSelf();

        $this->shippingAddressMock->expects($this->once())
            ->method('setShouldIgnoreValidation')
            ->with(true)
            ->willReturnSelf();

        $billingAddressMock->expects($this->at(1))
            ->method('getEmail')
            ->willReturn(self::TEST_EMAIL);

        $billingAddressMock->expects($this->never())
            ->method('setSameAsBilling');
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $quoteMock
     * @return Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getBillingAddressMock(\PHPUnit_Framework_MockObject_MockObject $quoteMock)
    {
        if (!isset($this->billingAddressMock)) {
            $this->billingAddressMock = $this->getMockBuilder(Address::class)
                ->setMethods(['setShouldIgnoreValidation', 'getEmail', 'setSameAsBilling'])
                ->disableOriginalConstructor()
                ->getMock();
        }

        $quoteMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressMock);

        return $this->billingAddressMock;
    }

    /**
     * @return Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteMock()
    {
        return $this->getMockBuilder(Quote::class)
            ->setMethods(
                [
                    'collectTotals',
                    'getBillingAddress',
                    'getShippingAddress',
                    'getIsVirtual'
                ]
            )->disableOriginalConstructor()
            ->getMock();
    }
}
