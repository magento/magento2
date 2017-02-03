<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Braintree_Result_Error;

/**
 * Test for Error
 */
class ErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Braintree\Helper\Error
     */
    private $model;

    /**
     * test setup
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            'Magento\Braintree\Helper\Error',
            [
            ]
        );
    }

    /**
     * @param array $result
     * @param boolean $expected
     * @dataProvider parseBraintreeErrorDataProvider
     */
    public function testisparseBraintreeError($result, $expected)
    {
        $resultObj = new \Braintree_Result_Error($result);
        $result = $this->model->parseBraintreeError($resultObj);
        $this->assertEquals(new \Magento\Framework\Phrase($expected), $result);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function parseBraintreeErrorDataProvider()
    {
        return [
            [
                'result' => [
                    'errors' =>
                        [
                            'errors' => [
                                [
                                    'code' => '91564',
                                    'message' => 'message 1',
                                ]
                            ],
                        ],
                ],
                'expected' => 'The processor declined your transaction, please re-enter your payment information',
            ],
            [
                'result' => [
                    'errors' =>
                        [
                            'errors' => [
                                [
                                    'code' => '1',
                                    'message' => 'message 1',
                                ],
                                [
                                    'code' => '2',
                                    'message' => 'message 2',
                                ],
                                [
                                    'code' => '3',
                                    'message' => 'message 3',
                                ],
                            ],
                        ],
                        'message' => "message 1\nmessage 2\nmessage 3",
                ],
                'expected' => ' message 1 message 2 message 3',
            ],
            [
                'result' => [
                    'errors' =>
                        [
                            'errors' => [],
                        ],
                        'params' =>
                        [
                            'transaction' =>
                                [
                                    'correlationId' => "e9e070b888210088a98217b8a4fa8e6e",
                                    'deviceSessionId' => "0ee868ff2ded66e8b09c092c9b3ee3a1",
                                    'fraudMerchantId' => "600000",
                                    'type' => "sale",
                                    'channel' => "Magento Kiwis",
                                    'orderId' => "000000073",
                                    'merchantAccountId' => "vr8xr64fvyzngb3j",
                                    'paymentMethodNonce' => "28581ba3-0ae0-43f6-9eb0-535d0de7c253",
                                    'options' =>
                                        [
                                            'storeInVault' => "false",
                                            'addBillingAddressToPaymentMethod' => "true",
                                        ],
                                        'customerId' => "10fa91e8a97f52743737f97163654e44",
                                        'creditCard' =>
                                        [
                                            'cardholderName' => "Name Lname",
                                        ],
                                        'billing' =>
                                        [
                                            'firstName' => "Name",
                                            'lastName' => "Lname",
                                            'company' => "null",
                                            'streetAddress' => "130 St",
                                            'extendedAddress' => "#110",
                                            'locality' => "Austin",
                                            'region' => "Texas",
                                            'postalCode' => "65656",
                                            'countryCodeAlpha2' => "US",
                                        ],
                                        'shipping' =>
                                        [
                                            'firstName' => "Name",
                                            'lastName' => "Lname",
                                            'company' => "null",
                                            'streetAddress' => "130 St",
                                            'extendedAddress' => "#110",
                                            'locality' => "Austin",
                                            'region' => "Texas",
                                            'postalCode' => "65656",
                                            'countryCodeAlpha2' => "US",
                                        ],
                                        'amount' => "1010",
                                ],
                        ],
                        'message' => "Gateway Rejected: fraud",
                        'transaction' =>
                        [
                            'id' => "dtkd8p",
                            'status' => "gateway_rejected",
                            'type' => "sale",
                            'currencyIsoCode' => "USD",
                            'amount' => "1010.00",
                            'merchantAccountId' => "vr8xr64fvyzngb3j",
                            'orderId' => "000000073",
                            'createdAt' => "DateTime Object",
                            'date' => "2015-06-17 14:35:51.000000",
                            'timezone_type' => "3",
                            'timezone' => "'UTC'",
                            'customer' =>
                                [
                                    'id' => "10fa91e8a97f52743737f97163654e44",
                                    'firstName' => "crius",
                                    'lastName' => "party",
                                    'company' => "null",
                                    'email' => "partysoft@gmail.com",
                                    'website' => "null",
                                    'phone' => "21034 343",
                                    'fax' => "null",
                                ],
                                'billing' =>
                                [
                                    'id' => "null",
                                    'firstName' => "Name",
                                    'lastName' => "Lname",
                                    'company' => "null",
                                    'streetAddress' => "130 St",
                                    'extendedAddress' => "#110",
                                    'locality' => "Austin",
                                    'region' => "Texas",
                                    'postalCode' => "65656",
                                    'countryName' => "Canada",
                                    'countryCodeAlpha2' => "US",
                                    'countryCodeAlpha3' => "USN",
                                    'countryCodeNumeric' => "124",
                                ],
                                'refundId' => "null",
                                'refundIds' => [],
                                'refundedTransactionId' => "null",
                                'settlementBatchId' => "null",
                                'shipping' =>
                                [
                                    'id' => "null",
                                    'firstName' => "Name",
                                    'lastName' => "Lname",
                                    'company' => "null",
                                    'streetAddress' => "130 St",
                                    'extendedAddress' => "#110",
                                    'locality' => "Austin",
                                    'region' => "Texas",
                                    'postalCode' => "65656",
                                    'countryName' => "Canada",
                                    'countryCodeAlpha2' => "US",
                                    'countryCodeAlpha3' => "USN",
                                    'countryCodeNumeric' => "124",
                                ],
                                'customFields' => "null",
                                'avsErrorResponseCode' => "null",
                                'avsPostalCodeResponseCode' => "null",
                                'avsStreetAddressResponseCode' => "null",
                                'cvvResponseCode' => "null",
                                'gatewayRejectionReason' => "fraud",
                                'processorAuthorizationCode' => "null",
                                'processorResponseCode' => "null",
                                'processorResponseText' => "Unknown []",
                                'additionalProcessorResponse' => "null",
                                'voiceReferralNumber' => "null",
                                'purchaseOrderNumber' => "null",
                                'taxAmount' => "null",
                                'taxExempt' => "null",
                                'creditCard' =>
                                    [
                                    'token' => "null",
                                    'bin' => "400011",
                                    'last4' => "1511",
                                    'cardType' => "Visa",
                                    'expirationMonth' => "06",
                                    'expirationYear' => "2015",
                                    'customerLocation' => "International",
                                    'cardholderName' => "Name Lname",
                                    'imageUrl' =>
                                        "https://assets.braintreegateway.com/payment_method_logo".
                                        "/visa.png?environment=sandbox",
                                    'uniqueNumberIdentifier' => "null",
                                    'prepaid' => "Unknown",
                                    'healthcare' => "Unknown",
                                    'debit' => "Unknown",
                                    'durbinRegulated' => "Unknown",
                                    'commercial' => "Unknown",
                                    'payroll' => "Unknown",
                                    'issuingBank' => "Unknown",
                                    'countryOfIssuance' => "Unknown",
                                    'productId' => "Unknown",
                                    'venmoSdk' => "null",
                                    ],
                                    'recurring' => "null",
                                    'channel' => "Magento Kiwis",
                                    'paymentInstrumentType' => "credit_card",
                                    'processorSettlementResponseCode' => "null",
                                    'processorSettlementResponseText' => "null",
                                    'threeDSecureInfo' => "null",
                        ],
                ],
                'expected' => 'Transaction declined by gateway: Check card details or try another card',
            ],
            [
                'result' => [
                    'errors' =>
                        [
                            'errors' => [],
                        ],
                        'params' =>
                        [
                            'transaction' =>
                                [
                                    'correlationId' => "e9e070b888210088a98217b8a4fa8e6e",
                                    'deviceSessionId' => "0ee868ff2ded66e8b09c092c9b3ee3a1",
                                    'fraudMerchantId' => "600000",
                                    'type' => "sale",
                                    'channel' => "Magento Kiwis",
                                    'orderId' => "000000073",
                                    'merchantAccountId' => "vr8xr64fvyzngb3j",
                                    'paymentMethodNonce' => "28581ba3-0ae0-43f6-9eb0-535d0de7c253",
                                    'options' =>
                                        [
                                            'storeInVault' => "false",
                                            'addBillingAddressToPaymentMethod' => "true",
                                        ],
                                        'customerId' => "10fa91e8a97f52743737f97163654e44",
                                        'creditCard' =>
                                        [
                                            'cardholderName' => "Name Lname",
                                        ],
                                        'billing' =>
                                        [
                                            'firstName' => "Name",
                                            'lastName' => "Lname",
                                            'company' => "null",
                                            'streetAddress' => "130 St",
                                            'extendedAddress' => "#110",
                                            'locality' => "Austin",
                                            'region' => "Texas",
                                            'postalCode' => "65656",
                                            'countryCodeAlpha2' => "US",
                                        ],
                                        'shipping' =>
                                        [
                                            'firstName' => "Name",
                                            'lastName' => "Lname",
                                            'company' => "null",
                                            'streetAddress' => "130 St",
                                            'extendedAddress' => "#110",
                                            'locality' => "Austin",
                                            'region' => "Texas",
                                            'postalCode' => "65656",
                                            'countryCodeAlpha2' => "US",
                                        ],
                                        'amount' => "1010",
                                ],
                        ],
                        'message' => "Processor Declined: fraud",
                        'transaction' =>
                        [
                            'id' => "dtkd8p",
                            'status' => "processor_declined",
                            'type' => "sale",
                            'currencyIsoCode' => "USD",
                            'amount' => "1010.00",
                            'merchantAccountId' => "vr8xr64fvyzngb3j",
                            'orderId' => "000000073",
                            'createdAt' => "DateTime Object",
                            'date' => "2015-06-17 14:35:51.000000",
                            'timezone_type' => "3",
                            'timezone' => "'UTC'",
                            'customer' =>
                                    [
                                    'id' => "10fa91e8a97f52743737f97163654e44",
                                    'firstName' => "crius",
                                    'lastName' => "party",
                                    'company' => "null",
                                    'email' => "partysoft@gmail.com",
                                    'website' => "null",
                                    'phone' => "21034 343",
                                    'fax' => "null",
                                    ],
                                    'billing' =>
                                    [
                                    'id' => "null",
                                    'firstName' => "Name",
                                    'lastName' => "Lname",
                                    'company' => "null",
                                    'streetAddress' => "130 St",
                                    'extendedAddress' => "#110",
                                    'locality' => "Austin",
                                    'region' => "Texas",
                                    'postalCode' => "65656",
                                    'countryName' => "Canada",
                                    'countryCodeAlpha2' => "US",
                                    'countryCodeAlpha3' => "USN",
                                    'countryCodeNumeric' => "124",
                                    ],
                                    'refundId' => "null",
                                    'refundIds' => [],
                                    'refundedTransactionId' => "null",
                                    'settlementBatchId' => "null",
                                    'shipping' =>
                                    [
                                    'id' => "null",
                                    'firstName' => "Name",
                                    'lastName' => "Lname",
                                    'company' => "null",
                                    'streetAddress' => "130 St",
                                    'extendedAddress' => "#110",
                                    'locality' => "Austin",
                                    'region' => "Texas",
                                    'postalCode' => "65656",
                                    'countryName' => "Canada",
                                    'countryCodeAlpha2' => "US",
                                    'countryCodeAlpha3' => "USN",
                                    'countryCodeNumeric' => "124",
                                    ],
                                    'customFields' => "null",
                                    'avsErrorResponseCode' => "null",
                                    'avsPostalCodeResponseCode' => "null",
                                    'avsStreetAddressResponseCode' => "null",
                                    'cvvResponseCode' => "null",
                                    'gatewayRejectionReason' => "fraud",
                                    'processorAuthorizationCode' => "null",
                                    'processorResponseCode' => "2000",
                                    'processorResponseText' => "Unknown []",
                                    'additionalProcessorResponse' => "null",
                                    'voiceReferralNumber' => "null",
                                    'purchaseOrderNumber' => "null",
                                    'taxAmount' => "null",
                                    'taxExempt' => "null",
                                    'creditCard' =>
                                    [
                                        'token' => "null",
                                        'bin' => "400011",
                                        'last4' => "1511",
                                        'cardType' => "Visa",
                                        'expirationMonth' => "06",
                                        'expirationYear' => "2015",
                                        'customerLocation' => "International",
                                        'cardholderName' => "Name Lname",
                                        'imageUrl' =>
                                            "https://assets.braintreegateway.com/payment_method_logo".
                                            "/visa.png?environment=sandbox",
                                        'uniqueNumberIdentifier' => "null",
                                        'prepaid' => "Unknown",
                                        'healthcare' => "Unknown",
                                        'debit' => "Unknown",
                                        'durbinRegulated' => "Unknown",
                                        'commercial' => "Unknown",
                                        'payroll' => "Unknown",
                                        'issuingBank' => "Unknown",
                                        'countryOfIssuance' => "Unknown",
                                        'productId' => "Unknown",
                                        'venmoSdk' => "null",
                                    ],
                                    'recurring' => "null",
                                    'channel' => "Magento Kiwis",
                                    'paymentInstrumentType' => "credit_card",
                                    'processorSettlementResponseCode' => "null",
                                    'processorSettlementResponseText' => "null",
                                    'threeDSecureInfo' => "null",
                        ],
                ],
                'expected' => 'Transaction declined: Contact your bank or try another card',
            ]
        ];

    }

    /**
     * @param array $result
     * @param boolean $expected
     * @dataProvider isCloneUnsuccessfulErrorDataProvider
     */
    public function testisCloneUnsuccessfulError($result, $expected)
    {
        $resultObj = new \Braintree_Result_Error($result);
        $resultBool = $this->model->isCloneUnsuccessfulError($resultObj);
        $this->assertEquals($expected, $resultBool);
    }

    /**
     * @return array
     */
    public function isCloneUnsuccessfulErrorDataProvider()
    {
        return [
            [
                'result' => [
                    'errors' =>
                        [
                            'errors' => [],
                        ],
                ],
                'expected' => false,
            ],
            [
                'result' => [
                    'errors' =>
                        [
                            'errors' => [
                                [
                                    'code' => 'code'
                                ]
                            ],
                        ],
                ],
                'expected' => false,
            ],
            [
                'result' => [
                    'errors' =>
                        [
                            'errors' => [
                                [
                                    'code' => '91542'
                                ]
                            ],
                        ],
                ],
                'expected' => true,
            ],
        ];

    }

    /**
     * @param array $result
     * @param boolean $expected
     * @dataProvider isNonceUsedMoreThanOnceErrorDataProvider
     */
    public function testIsNonceUsedMoreThanOnceError($result, $expected)
    {
        $resultObj = new \Braintree_Result_Error($result);
        $resultBool = $this->model->isNonceUsedMoreThanOnceError($resultObj);
        $this->assertEquals($expected, $resultBool);
    }

    /**
     * @return array
     */
    public function isNonceUsedMoreThanOnceErrorDataProvider()
    {
        return [
                [
                    'result' => [
                        'errors' =>
                            [
                                'errors' => [],
                            ],
                    ],
                    'expected' => false,
                ],
                [
                    'result' => [
                        'errors' =>
                            [
                                'errors' => [
                                    [
                                        'code' => 'code'
                                    ]
                                ],
                            ],
                        ],
                    'expected' => false,
                ],
                [
                    'result' => [
                        'errors' =>
                            [
                                'errors' => [
                                    [
                                        'code' => '91564'
                                    ]
                                ],
                            ],
                    ],
                    'expected' => true,
                ],
            ];
    }
}
