<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model;

use Magento\Braintree\Model\PaymentMethod\PayPal;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CheckoutTest
 *
 */
class CheckoutTest extends \PHPUnit_Framework_TestCase
{
    const EMAIL = 'joe@example.com';
    const FIRSTNAME = 'John';
    const LASTNAME = 'Doe';
    const SHIPPING_STREET_ADDRESS = '123 Division Street';
    const SHIPPING_EXTENDED_ADDRESS = 'Apt. #1';
    const SHIPPING_LOCALITY = 'Chicago';
    const SHIPPING_REGION = 'IL';
    const SHIPPING_COUNTRY_CODE = 'US';
    const SHIPPING_POSTAL_CODE = '60618';
    const BILLING_LINE1 = '123 Billing Street';
    const BILLING_LINE2 = 'Apt. #1';
    const BILLING_CITY = 'Chicago';
    const BILLING_STATE = 'IL';
    const BILLING_COUNTRY_CODE = 'US';
    const BILLING_POSTAL_CODE = '60618';

    /**
     * @var \Magento\Braintree\Model\Checkout
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $billingAddressMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddressMock;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    protected function setUp()
    {
        $this->setupAddressMock();
        $this->quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressMock);
        $this->quoteMock->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $configMock = $this->getMockBuilder('\Magento\Paypal\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Model\Checkout',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'params' => [
                    'quote' => $this->quoteMock,
                    'config' => $configMock,
                ]
            ]
        );
    }

    protected function setupAddressMock()
    {
        $this->billingAddressMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setShouldIgnoreValidation',
                    'setSameAsBilling',
                    'getEmail',
                    'setEmail',
                    'setPrefix',
                    'setMiddlename',
                    'setLastname',
                    'setFirstname',
                    'setSuffix',
                    'setCollectShippingRates',
                    'setStreet',
                    'setCity',
                    'setRegionCode',
                    'setCountryId',
                    'setPostcode'
                ]
            )->getMock();
        $this->shippingAddressMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setShouldIgnoreValidation',
                    'setSameAsBilling',
                    'setEmail',
                    'setPrefix',
                    'setMiddlename',
                    'setLastname',
                    'setFirstname',
                    'setSuffix',
                    'setCollectShippingRates',
                    'setStreet',
                    'setCity',
                    'setRegionCode',
                    'setCountryId',
                    'setPostcode'
                ]
            )->getMock();
    }

    protected function verifyIgnoreAddressValidation()
    {
        $this->billingAddressMock->expects($this->once())
            ->method('setShouldIgnoreValidation')
            ->with(true);

        $this->billingAddressMock->expects($this->once())
            ->method('setShouldIgnoreValidation')
            ->with(true);
    }

    protected function verifyPaymentInfo()
    {
        $paymentMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($paymentMock);
    }


    /**
     * @dataProvider initializeQuoteForReviewDataProvider
     */
    public function testInitializeQuoteForReview(
        $paymentMethodNonce,
        $details,
        $expectedShipping,
        $expectedBilling,
        $expectedPaymentAdditionalInfo
    ) {
        $this->verifyIgnoreAddressValidation();
        $this->quoteMock->expects($this->any())
            ->method('getIsVirtual')
            ->willReturn(false);

        $paymentMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())
            ->method('setMethod')
            ->with(PayPal::METHOD_CODE);

        $this->quoteMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($paymentMock);

        foreach ($expectedShipping as $methodName => $value) {
            $this->shippingAddressMock->expects($this->once())
                ->method($methodName)
                ->with($value)
                ->willReturnSelf();
        }
        foreach ($expectedBilling as $methodName => $value) {
            $this->billingAddressMock->expects($this->once())
                ->method($methodName)
                ->with($value)
                ->willReturnSelf();
        }
        $index = 1;
        foreach ($expectedPaymentAdditionalInfo as $key => $value) {
            $paymentMock->expects($this->at($index))
                ->method('setAdditionalInformation')
                ->with($key, $value);
            $index++;
        }

        $this->quoteMock->expects($this->once())
            ->method('collectTotals');
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock);

        $this->model->initializeQuoteForReview($paymentMethodNonce, $details);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function initializeQuoteForReviewDataProvider()
    {
        return [
            'with_billing_address' => [
                'payment_method_nonce' => 'nonce',
                'details' => [
                    'email' => self::EMAIL,
                    'firstName' => self::FIRSTNAME,
                    'lastName' => self::LASTNAME,
                    'shippingAddress' => [
                        'streetAddress' => self::SHIPPING_STREET_ADDRESS,
                        'extendedAddress' => self::SHIPPING_EXTENDED_ADDRESS,
                        'locality' => self::SHIPPING_LOCALITY,
                        'region' => self::SHIPPING_REGION,
                        'countryCodeAlpha2' => self::SHIPPING_COUNTRY_CODE,
                        'postalCode' => self::SHIPPING_POSTAL_CODE,
                    ],
                    'billingAddress' => [
                        'line1' => self::BILLING_LINE1,
                        'line2' => self::BILLING_LINE2,
                        'city' => self::BILLING_CITY,
                        'state' => self::BILLING_STATE,
                        'countryCode' => self::BILLING_COUNTRY_CODE,
                        'postalCode' => self::BILLING_POSTAL_CODE,
                    ],
                ],
                'expected_shipping' => [
                    'setFirstname' => self::FIRSTNAME,
                    'setLastname' => self::LASTNAME,
                    'setEmail' => self::EMAIL,
                    'setCollectShippingRates' => true,
                    'setStreet' => [self::SHIPPING_STREET_ADDRESS, self::SHIPPING_EXTENDED_ADDRESS],
                    'setCity' => self::SHIPPING_LOCALITY,
                    'setRegionCode' => self::SHIPPING_REGION,
                    'setCountryId' => self::SHIPPING_COUNTRY_CODE,
                    'setPostCode' => self::SHIPPING_POSTAL_CODE,
                ],
                'expected_billing' => [
                    'setFirstname' => self::FIRSTNAME,
                    'setLastname' => self::LASTNAME,
                    'setEmail' => self::EMAIL,
                    'setStreet' => [self::BILLING_LINE1, self::BILLING_LINE2],
                    'setCity' => self::BILLING_CITY,
                    'setRegionCode' => self::BILLING_STATE,
                    'setCountryId' => self::BILLING_COUNTRY_CODE,
                    'setPostCode' => self::BILLING_POSTAL_CODE,
                ],
                'expected_payment_additional_info' => [
                    'payment_method_nonce' => 'nonce',
                    'payerEmail' => self::EMAIL,
                    'payerFirstName' => self::FIRSTNAME,
                    'payerLastName' => self::LASTNAME,
                ]
            ],
            'without_billing_address' => [
                'payment_method_nonce' => 'nonce',
                'details' => [
                    'email' => self::EMAIL,
                    'firstName' => self::FIRSTNAME,
                    'lastName' => self::LASTNAME,
                    'shippingAddress' => [
                        'streetAddress' => self::SHIPPING_STREET_ADDRESS,
                        'extendedAddress' => self::SHIPPING_EXTENDED_ADDRESS,
                        'locality' => self::SHIPPING_LOCALITY,
                        'region' => self::SHIPPING_REGION,
                        'countryCodeAlpha2' => self::SHIPPING_COUNTRY_CODE,
                        'postalCode' => self::SHIPPING_POSTAL_CODE,
                    ],
                ],
                'expected_shipping' => [
                    'setFirstname' => self::FIRSTNAME,
                    'setLastname' => self::LASTNAME,
                    'setEmail' => self::EMAIL,
                    'setCollectShippingRates' => true,
                    'setStreet' => [self::SHIPPING_STREET_ADDRESS, self::SHIPPING_EXTENDED_ADDRESS],
                    'setCity' => self::SHIPPING_LOCALITY,
                    'setRegionCode' => self::SHIPPING_REGION,
                    'setCountryId' => self::SHIPPING_COUNTRY_CODE,
                    'setPostCode' => self::SHIPPING_POSTAL_CODE,
                ],
                'expected_billing' => [
                    'setFirstname' => self::FIRSTNAME,
                    'setLastname' => self::LASTNAME,
                    'setEmail' => self::EMAIL,
                    'setStreet' => [self::SHIPPING_STREET_ADDRESS, self::SHIPPING_EXTENDED_ADDRESS],
                    'setCity' => self::SHIPPING_LOCALITY,
                    'setRegionCode' => self::SHIPPING_REGION,
                    'setCountryId' => self::SHIPPING_COUNTRY_CODE,
                    'setPostCode' => self::SHIPPING_POSTAL_CODE,
                ],
                'expected_payment_additional_info' => [
                    'payment_method_nonce' => 'nonce',
                    'payerEmail' => self::EMAIL,
                    'payerFirstName' => self::FIRSTNAME,
                    'payerLastName' => self::LASTNAME,
                ]
            ],
            'without_shipping_extended_address' => [
                'payment_method_nonce' => 'nonce',
                'details' => [
                    'email' => self::EMAIL,
                    'firstName' => self::FIRSTNAME,
                    'lastName' => self::LASTNAME,
                    'shippingAddress' => [
                        'streetAddress' => self::SHIPPING_STREET_ADDRESS,
                        'locality' => self::SHIPPING_LOCALITY,
                        'region' => self::SHIPPING_REGION,
                        'countryCodeAlpha2' => self::SHIPPING_COUNTRY_CODE,
                        'postalCode' => self::SHIPPING_POSTAL_CODE,
                    ],
                ],
                'expected_shipping' => [
                    'setFirstname' => self::FIRSTNAME,
                    'setLastname' => self::LASTNAME,
                    'setEmail' => self::EMAIL,
                    'setCollectShippingRates' => true,
                    'setStreet' => [self::SHIPPING_STREET_ADDRESS, null],
                    'setCity' => self::SHIPPING_LOCALITY,
                    'setRegionCode' => self::SHIPPING_REGION,
                    'setCountryId' => self::SHIPPING_COUNTRY_CODE,
                    'setPostCode' => self::SHIPPING_POSTAL_CODE,
                ],
                'expected_billing' => [
                    'setFirstname' => self::FIRSTNAME,
                    'setLastname' => self::LASTNAME,
                    'setEmail' => self::EMAIL,
                    'setStreet' => [self::SHIPPING_STREET_ADDRESS, null],
                    'setCity' => self::SHIPPING_LOCALITY,
                    'setRegionCode' => self::SHIPPING_REGION,
                    'setCountryId' => self::SHIPPING_COUNTRY_CODE,
                    'setPostCode' => self::SHIPPING_POSTAL_CODE,
                ],
                'expected_payment_additional_info' => [
                    'payment_method_nonce' => 'nonce',
                    'payerEmail' => self::EMAIL,
                    'payerFirstName' => self::FIRSTNAME,
                    'payerLastName' => self::LASTNAME,
                ]
            ],
        ];
    }
}
