<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Paypal\Helper;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Paypal\Helper\QuoteUpdater;
use Magento\Quote\Api\Data\CartExtensionInterface;

/**
 * Class QuoteUpdaterTest
 *
 * @see \Magento\Braintree\Model\Paypal\Helper\QuoteUpdater
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteUpdaterTest extends \PHPUnit\Framework\TestCase
{
    const TEST_NONCE = '3ede7045-2aea-463e-9754-cd658ffeeb48';

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
    private $billingAddressMock;

    /**
     * @var Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAddressMock;

    /**
     * @var QuoteUpdater
     */
    private $quoteUpdater;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->billingAddressMock = $this->getMockBuilder(Address::class)
            ->setMethods(
                [
                    'setLastname',
                    'setFirstname',
                    'setEmail',
                    'setCollectShippingRates',
                    'setStreet',
                    'setCity',
                    'setRegionCode',
                    'setCountryId',
                    'setPostcode',
                    'setShouldIgnoreValidation',
                    'getEmail'
                ]
            )->disableOriginalConstructor()
            ->getMock();
        $this->shippingAddressMock = $this->getMockBuilder(Address::class)
            ->setMethods(
                [
                    'setLastname',
                    'setFirstname',
                    'setEmail',
                    'setCollectShippingRates',
                    'setStreet',
                    'setCity',
                    'setRegionCode',
                    'setCountryId',
                    'setPostcode',
                    'setShouldIgnoreValidation'
                ]
            )->disableOriginalConstructor()
            ->getMock();

        $this->quoteUpdater = new QuoteUpdater(
            $this->configMock,
            $this->quoteRepositoryMock
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecute()
    {
        $details = $this->getDetails();
        $quoteMock = $this->getQuoteMock();
        $paymentMock = $this->getPaymentMock();

        $quoteMock->expects(self::once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $paymentMock->expects(self::once())
            ->method('setMethod')
            ->with(ConfigProvider::PAYPAL_CODE);
        $paymentMock->expects(self::once())
            ->method('setAdditionalInformation')
            ->with(DataAssignObserver::PAYMENT_METHOD_NONCE, self::TEST_NONCE);

        $this->updateQuoteStep($quoteMock, $details);

        $this->quoteUpdater->execute(self::TEST_NONCE, $details, $quoteMock);
    }

    /**
     * @return void
     */
    private function disabledQuoteAddressValidationStep()
    {
        $this->billingAddressMock->expects(self::once())
            ->method('setShouldIgnoreValidation')
            ->with(true);
        $this->shippingAddressMock->expects(self::once())
            ->method('setShouldIgnoreValidation')
            ->with(true);
        $this->billingAddressMock->expects(self::once())
            ->method('getEmail')
            ->willReturn('bt_buyer_us@paypal.com');
    }

    /**
     * @return array
     */
    private function getDetails()
    {
        return [
            'email' => 'bt_buyer_us@paypal.com',
            'payerId' => 'FAKE_PAYER_ID',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phone' => '312-123-4567',
            'countryCode' => 'US',
            'shippingAddress' => [
                'streetAddress' => '123 Division Street',
                'extendedAddress' => 'Apt. #1',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60618',
                'countryCodeAlpha2' => 'US',
                'recipientName' => 'John Doe',
            ],
            'billingAddress' => [
                'streetAddress' => '123 Billing Street',
                'extendedAddress' => 'Apt. #1',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60618',
                'countryCodeAlpha2' => 'US',
            ],
        ];
    }

    /**
     * @param array $details
     */
    private function updateShippingAddressStep(array $details)
    {
        $this->shippingAddressMock->expects(self::once())
            ->method('setLastname')
            ->with($details['lastName']);
        $this->shippingAddressMock->expects(self::once())
            ->method('setFirstname')
            ->with($details['firstName']);
        $this->shippingAddressMock->expects(self::once())
            ->method('setEmail')
            ->with($details['email']);
        $this->shippingAddressMock->expects(self::once())
            ->method('setCollectShippingRates')
            ->with(true);

        $this->updateAddressDataStep($this->shippingAddressMock, $details['shippingAddress']);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $addressMock
     * @param array $addressData
     */
    private function updateAddressDataStep(\PHPUnit_Framework_MockObject_MockObject $addressMock, array $addressData)
    {
        $addressMock->expects(self::once())
            ->method('setStreet')
            ->with([$addressData['streetAddress'], $addressData['extendedAddress']]);
        $addressMock->expects(self::once())
            ->method('setCity')
            ->with($addressData['locality']);
        $addressMock->expects(self::once())
            ->method('setRegionCode')
            ->with($addressData['region']);
        $addressMock->expects(self::once())
            ->method('setCountryId')
            ->with($addressData['countryCodeAlpha2']);
        $addressMock->expects(self::once())
            ->method('setPostcode')
            ->with($addressData['postalCode']);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $quoteMock
     * @param array $details
     */
    private function updateQuoteAddressStep(\PHPUnit_Framework_MockObject_MockObject $quoteMock, array $details)
    {
        $quoteMock->expects(self::exactly(2))
            ->method('getIsVirtual')
            ->willReturn(false);

        $this->updateShippingAddressStep($details);
        $this->updateBillingAddressStep($details);
    }

    /**
     * @param array $details
     */
    private function updateBillingAddressStep(array $details)
    {
        $this->configMock->expects(self::once())
            ->method('isRequiredBillingAddress')
            ->willReturn(true);

        $this->updateAddressDataStep($this->billingAddressMock, $details['billingAddress']);

        $this->billingAddressMock->expects(self::once())
            ->method('setLastname')
            ->with($details['lastName']);
        $this->billingAddressMock->expects(self::once())
            ->method('setFirstname')
            ->with($details['firstName']);
        $this->billingAddressMock->expects(self::once())
            ->method('setEmail')
            ->with($details['email']);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $quoteMock
     * @param array $details
     */
    private function updateQuoteStep(\PHPUnit_Framework_MockObject_MockObject $quoteMock, array $details)
    {
        $quoteMock->expects(self::once())
            ->method('setMayEditShippingAddress')
            ->with(false);
        $quoteMock->expects(self::once())
            ->method('setMayEditShippingMethod')
            ->with(true);

        $quoteMock->expects(self::exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $quoteMock->expects(self::exactly(2))
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressMock);

        $this->updateQuoteAddressStep($quoteMock, $details);
        $this->disabledQuoteAddressValidationStep();

        $quoteMock->expects(self::once())
            ->method('collectTotals');

        $this->quoteRepositoryMock->expects(self::once())
            ->method('save')
            ->with($quoteMock);
    }

    /**
     * @return Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteMock()
    {
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->setMethods(
                [
                    'getIsVirtual',
                    'getPayment',
                    'setMayEditShippingAddress',
                    'setMayEditShippingMethod',
                    'collectTotals',
                    'getShippingAddress',
                    'getBillingAddress',
                    'getExtensionAttributes'
                ]
            )->disableOriginalConstructor()
            ->getMock();

        $cartExtensionMock = $this->getMockBuilder(CartExtensionInterface::class)
            ->setMethods(['setShippingAssignments'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $quoteMock->expects(self::any())
            ->method('getExtensionAttributes')
            ->willReturn($cartExtensionMock);

        return $quoteMock;
    }

    /**
     * @return Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentMock()
    {
        return $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
