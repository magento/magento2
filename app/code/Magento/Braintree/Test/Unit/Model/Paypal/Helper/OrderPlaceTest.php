<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Model\Paypal\Helper;

use Magento\Braintree\Model\Paypal\Helper\OrderPlace;
use Magento\Braintree\Model\Paypal\OrderCancellationService;
use Magento\Checkout\Api\AgreementsValidatorInterface;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class OrderPlaceTest
 *
 * @see \Magento\Braintree\Model\Paypal\Helper\OrderPlace
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderPlaceTest extends \PHPUnit\Framework\TestCase
{
    const TEST_EMAIL = 'test@test.loc';

    /**
     * @var CartManagementInterface|MockObject
     */
    private $cartManagement;

    /**
     * @var AgreementsValidatorInterface|MockObject
     */
    private $agreementsValidator;

    /**
     * @var Session|MockObject
     */
    private $customerSession;

    /**
     * @var Data|MockObject
     */
    private $checkoutHelper;

    /**
     * @var Address|MockObject
     */
    private $billingAddress;

    /**
     * @var OrderPlace
     */
    private $orderPlace;

    /**
     * @var OrderCancellationService|MockObject
     */
    private $orderCancellation;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->cartManagement = $this->getMockBuilder(CartManagementInterface::class)
            ->getMockForAbstractClass();
        $this->agreementsValidator = $this->getMockBuilder(AgreementsValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderCancellation = $this->getMockBuilder(OrderCancellationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderPlace = new OrderPlace(
            $this->cartManagement,
            $this->agreementsValidator,
            $this->customerSession,
            $this->checkoutHelper,
            $this->orderCancellation
        );
    }

    /**
     * Checks a scenario for a guest customer.
     *
     * @throws \Exception
     */
    public function testExecuteGuest()
    {
        $agreement = ['test', 'test'];
        $quoteMock = $this->getQuoteMock();

        $this->agreementsValidator->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->getCheckoutMethodStep($quoteMock);
        $this->prepareGuestQuoteStep($quoteMock);
        $this->disabledQuoteAddressValidationStep($quoteMock);

        $quoteMock->expects(self::once())
            ->method('collectTotals');

        $quoteMock->expects(self::once())
            ->method('getId')
            ->willReturn(10);

        $this->cartManagement->expects(self::once())
            ->method('placeOrder')
            ->with(10);

        $this->orderPlace->execute($quoteMock, $agreement);
    }

    /**
     * Disables address validation.
     *
     * @param MockObject $quoteMock
     */
    private function disabledQuoteAddressValidationStep(MockObject $quoteMock)
    {
        $billingAddressMock = $this->getBillingAddressMock($quoteMock);
        $shippingAddressMock = $this->getMockBuilder(Address::class)
            ->setMethods(['setShouldIgnoreValidation'])
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock->method('getShippingAddress')
            ->willReturn($shippingAddressMock);

        $billingAddressMock->method('setShouldIgnoreValidation')
            ->with(true)
            ->willReturnSelf();

        $quoteMock->method('getIsVirtual')
            ->willReturn(false);

        $shippingAddressMock->method('setShouldIgnoreValidation')
            ->with(true)
            ->willReturnSelf();

        $billingAddressMock->method('getEmail')
            ->willReturn(self::TEST_EMAIL);

        $billingAddressMock->expects(self::never())
            ->method('setSameAsBilling');
    }

    /**
     * Prepares checkout step.
     *
     * @param MockObject $quoteMock
     */
    private function getCheckoutMethodStep(MockObject $quoteMock)
    {
        $this->customerSession->method('isLoggedIn')
            ->willReturn(false);

        $quoteMock->expects(self::at(1))
            ->method('getCheckoutMethod')
            ->willReturn(null);

        $this->checkoutHelper->method('isAllowedGuestCheckout')
            ->with($quoteMock)
            ->willReturn(true);

        $quoteMock->method('setCheckoutMethod')
            ->with(Onepage::METHOD_GUEST);

        $quoteMock->expects(self::at(2))
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_GUEST);
    }

    /**
     * Prepares quote.
     *
     * @param MockObject $quoteMock
     */
    private function prepareGuestQuoteStep(MockObject $quoteMock)
    {
        $billingAddressMock = $this->getBillingAddressMock($quoteMock);

        $quoteMock->expects(self::once())
            ->method('setCustomerId')
            ->with(null)
            ->willReturnSelf();

        $billingAddressMock->expects(self::at(0))
            ->method('getEmail')
            ->willReturn(self::TEST_EMAIL);

        $quoteMock->method('setCustomerEmail')
            ->with(self::TEST_EMAIL)
            ->willReturnSelf();

        $quoteMock->method('setCustomerIsGuest')
            ->with(true)
            ->willReturnSelf();

        $quoteMock->method('setCustomerGroupId')
            ->with(Group::NOT_LOGGED_IN_ID)
            ->willReturnSelf();
    }

    /**
     * Gets a mock object for a billing address entity.
     *
     * @param MockObject $quoteMock
     * @return Address|MockObject
     */
    private function getBillingAddressMock(MockObject $quoteMock)
    {
        if (!isset($this->billingAddress)) {
            $this->billingAddress = $this->getMockBuilder(Address::class)
                ->setMethods(['setShouldIgnoreValidation', 'getEmail', 'setSameAsBilling'])
                ->disableOriginalConstructor()
                ->getMock();
        }

        $quoteMock->method('getBillingAddress')
            ->willReturn($this->billingAddress);

        return $this->billingAddress;
    }

    /**
     * Gets a mock object for a quote.
     *
     * @return Quote|MockObject
     */
    private function getQuoteMock()
    {
        return $this->getMockBuilder(Quote::class)
            ->setMethods(
                [
                    'setCustomerId',
                    'setCustomerEmail',
                    'setCustomerIsGuest',
                    'setCustomerGroupId',
                    'getCheckoutMethod',
                    'setCheckoutMethod',
                    'collectTotals',
                    'getId',
                    'getBillingAddress',
                    'getShippingAddress',
                    'getIsVirtual'
                ]
            )->disableOriginalConstructor()
            ->getMock();
    }
}
