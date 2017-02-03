<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model;

use Magento\Braintree\Model\ConfigProvider;
use Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ConfigProviderTest
 */
class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    const CLIENT_TOKEN = 'token';
    const CC_TOKEN = 'cc_token';
    const TODAY_MONTH = 6;
    const TODAY_YEAR = 2015;
    const PAYMENT_NONCE_GENERATION_URL = 'braintree/creditcard/generate';

    protected $availableCardTypes = [
        'VI' => 'Visa',
        'MA' => 'Master Card',
        'AE' => 'American Express',
        'DI' => 'Discover Card',
    ];

    /**
     * @var \Magento\Braintree\Model\ConfigProvider
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Payment\Model\CcConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ccConfigMock;

    /**
     * @var \Magento\Braintree\Model\Config\Cc|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Braintree\Model\Vault|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $vaultMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Braintree\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    protected function setUp()
    {
        $this->ccConfigMock = $this->getMockBuilder('\Magento\Payment\Model\CcConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();
        $this->vaultMock = $this->getMockBuilder('\Magento\Braintree\Model\Vault')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder('\Magento\Braintree\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder('\Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder('\Magento\Framework\Url')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Model\ConfigProvider',
            [
                'ccConfig' => $this->ccConfigMock,
                'vault' => $this->vaultMock,
                'config' => $this->configMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'customerSession' => $this->customerSessionMock,
                'dataHelper' => $this->helperMock,
                'urlBuilder' => $this->urlBuilderMock,
            ]
        );
    }

    public function testGetStoredCards()
    {
        $result = 'result';
        $this->vaultMock->expects($this->once())
            ->method('currentCustomerStoredCards')
            ->willReturn($result);
        $this->assertEquals($result, $this->model->getStoredCards());
    }

    /**
     * @param bool $useVault
     * @param bool $isLoggedIn
     * @param bool $result
     * @dataProvider canSaveCardDataProvider
     */
    public function testCanSaveCard($useVault, $isLoggedIn, $result)
    {
        $this->configMock->expects($this->once())
            ->method('useVault')
            ->willReturn($useVault);

        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn($isLoggedIn);

        $this->assertEquals($result, $this->model->canSaveCard());
    }

    public function canSaveCardDataProvider()
    {
        return [
            'not_using_vault' => [
                'use_vault' => false,
                'is_logged_in' => true,
                'result' => false,
            ],
            'using_vault_not_logged_in' => [
                'use_vault' => true,
                'is_logged_in' => false,
                'result' => false,
            ],
            'using_vault_logged_in' => [
                'use_vault' => true,
                'is_logged_in' => true,
                'result' => true,
            ],
        ];
    }

    public function testGetConfigNotActive()
    {
        $this->configMock->expects($this->once())
            ->method('isActive')
            ->willReturn(false);
        $this->assertEquals([], $this->model->getConfig());
    }

    /**
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig(
        $configData,
        $vaultData,
        $tokenNonceMap,
        $expectedResult
    ) {
        foreach ($configData as $key => $value) {
            $this->configMock->expects($this->any())
                ->method($key)
                ->willReturn($value);
        }

        foreach ($vaultData as $key => $value) {
            $this->vaultMock->expects($this->any())
                ->method($key)
                ->willReturn($value);
        }

        $this->vaultMock->expects($this->any())
            ->method('generatePaymentMethodToken')
            ->willReturnMap($tokenNonceMap);

        $cardTypeMap = [
            ['Visa', 'VI'],
            ['Master Card', 'MA'],
            ['American Express', 'AE'],
            ['Discover Card', 'DI'],
        ];
        $this->helperMock->expects($this->any())
            ->method('getCcTypeCodeByName')
            ->willReturnMap($cardTypeMap);
        $this->helperMock->expects($this->once())
            ->method('getTodayMonth')
            ->willReturn(self::TODAY_MONTH);
        $this->helperMock->expects($this->once())
            ->method('getTodayYear')
            ->willReturn(self::TODAY_YEAR);
        $this->helperMock->expects($this->once())
            ->method('getCcAvailableCardTypes')
            ->willReturn($this->availableCardTypes);

        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('braintree/creditcard/generate')
            ->willReturn(self::PAYMENT_NONCE_GENERATION_URL);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getConfigDataProvider()
    {
        return [
            'no_vault' => [
                'config_data' => [
                    'isActive' => true,
                    'getClientToken' => self::CLIENT_TOKEN,
                    'is3dSecureEnabled' => true,
                    'useVault' => false,
                    'getCountrySpecificCardTypeConfig' => [
                        'US' => ['VI', 'AE', 'MA'],
                    ],
                    'isFraudDetectionEnabled' => true,
                    'isCcDetectionEnabled' => true,
                    'getBraintreeDataJs' => 'https://js.braintreegateway.com/v1/braintree-data.js'
                ],
                'vault_data' => [],
                'token_nonce_map' => [],
                'expected_result' => [
                    'payment' => [
                        'braintree' => [
                            'clientToken' => self::CLIENT_TOKEN,
                            'useVault' => false,
                            'canSaveCard' => false,
                            'show3dSecure' => true,
                            'storedCards' => [],
                            'selectedCardToken' => null,
                            'creditCardExpMonth' => self::TODAY_MONTH,
                            'creditCardExpYear' => self::TODAY_YEAR,
                            'countrySpecificCardTypes' => [
                                'US' => ['VI', 'AE', 'MA'],
                            ],
                            'isFraudDetectionEnabled' => true,
                            'isCcDetectionEnabled' => true,
                            'availableCardTypes' => $this->availableCardTypes,
                            'braintreeDataJs'=> 'https://js.braintreegateway.com/v1/braintree-data.js',
                            'ajaxGenerateNonceUrl' => self::PAYMENT_NONCE_GENERATION_URL
                        ],
                    ],
                ]
            ],
            'vault_with_stored_cards' => [
                'config_data' => [
                    'isActive' => true,
                    'getClientToken' => self::CLIENT_TOKEN,
                    'is3dSecureEnabled' => false,
                    'useVault' => true,
                    'getCountrySpecificCardTypeConfig' => [
                        'US' => ['VI', 'AE', 'MA'],
                    ],
                    'isFraudDetectionEnabled' => true,
                    'isCcDetectionEnabled' => true,
                    'getBraintreeDataJs' => 'https://js.braintreegateway.com/v1/braintree-data.js'
                ],
                'vault_data' => [
                    'currentCustomerStoredCards' => [
                        \Braintree_CreditCard::factory(
                            [
                                'token' => 'token1',
                                'bin' => '4218',
                                'last4' => '1001',
                                'cardType' => 'Visa',
                                'default' => false,
                            ]
                        ),
                        \Braintree_CreditCard::factory(
                            [
                                'token' => 'token2',
                                'bin' => '5555',
                                'last4' => '1054',
                                'cardType' => 'Master Card',
                                'default' => 1,
                            ]
                        ),
                    ],
                ],
                'token_nonce_map' => [
                    ['token1', 'nonce1'],
                    ['token2', 'nonce2'],
                ],
                'expected_result' => [
                    'payment' => [
                        'braintree' => [
                            'clientToken' => self::CLIENT_TOKEN,
                            'useVault' => true,
                            'canSaveCard' => true,
                            'show3dSecure' => false,
                            'storedCards' => [
                                [
                                    'token' => 'token1',
                                    'maskedNumber' => '4218******1001 - Visa',
                                    'selected' => false,
                                    'type' => 'VI',
                                ],
                                [
                                    'token' => 'token2',
                                    'maskedNumber' => '5555******1054 - Master Card',
                                    'selected' => 1,
                                    'type' => 'MA',
                                ],
                            ],
                            'selectedCardToken' => 'token2',
                            'creditCardExpMonth' => self::TODAY_MONTH,
                            'creditCardExpYear' => self::TODAY_YEAR,
                            'countrySpecificCardTypes' => [
                                'US' => ['VI', 'AE', 'MA'],
                            ],
                            'isFraudDetectionEnabled' => true,
                            'isCcDetectionEnabled' => true,
                            'availableCardTypes' => $this->availableCardTypes,
                            'braintreeDataJs'=> 'https://js.braintreegateway.com/v1/braintree-data.js',
                            'ajaxGenerateNonceUrl' => self::PAYMENT_NONCE_GENERATION_URL
                        ],
                    ],
                ]
            ],
            'vault_with_stored_cards_3dsecure' => [
                'config_data' => [
                    'isActive' => true,
                    'getClientToken' => self::CLIENT_TOKEN,
                    'is3dSecureEnabled' => true,
                    'useVault' => true,
                    'getCountrySpecificCardTypeConfig' => [
                        'US' => ['VI', 'AE', 'MA'],
                    ],
                    'isFraudDetectionEnabled' => true,
                    'isCcDetectionEnabled' => true,
                    'getBraintreeDataJs' => 'https://js.braintreegateway.com/v1/braintree-data.js'
                ],
                'vault_data' => [
                    'currentCustomerStoredCards' => [
                        \Braintree_CreditCard::factory(
                            [
                                'token' => 'token1',
                                'bin' => '4218',
                                'last4' => '1001',
                                'cardType' => 'Visa',
                                'default' => false,
                            ]
                        ),
                        \Braintree_CreditCard::factory(
                            [
                                'token' => 'token2',
                                'bin' => '5555',
                                'last4' => '1054',
                                'cardType' => 'Master Card',
                                'default' => 1,
                            ]
                        ),
                    ],
                ],
                'token_nonce_map' => [
                    ['token1', 'nonce1'],
                    ['token2', 'nonce2'],
                ],
                'expected_result' => [
                    'payment' => [
                        'braintree' => [
                            'clientToken' => self::CLIENT_TOKEN,
                            'useVault' => true,
                            'canSaveCard' => true,
                            'show3dSecure' => true,
                            'storedCards' => [
                                [
                                    'token' => 'token1',
                                    'maskedNumber' => '4218******1001 - Visa',
                                    'selected' => false,
                                    'type' => 'VI',
                                ],
                                [
                                    'token' => 'token2',
                                    'maskedNumber' => '5555******1054 - Master Card',
                                    'selected' => 1,
                                    'type' => 'MA',
                                ],
                            ],
                            'selectedCardToken' => 'token2',
                            'creditCardExpMonth' => self::TODAY_MONTH,
                            'creditCardExpYear' => self::TODAY_YEAR,
                            'countrySpecificCardTypes' => [
                                'US' => ['VI', 'AE', 'MA'],
                            ],
                            'isFraudDetectionEnabled' => true,
                            'isCcDetectionEnabled' => true,
                            'availableCardTypes' => $this->availableCardTypes,
                            'braintreeDataJs'=> 'https://js.braintreegateway.com/v1/braintree-data.js',
                            'ajaxGenerateNonceUrl' => self::PAYMENT_NONCE_GENERATION_URL
                        ],
                    ],
                ]
            ],
            'vault_with_no_stored_cards' => [
                'config_data' => [
                    'isActive' => true,
                    'getClientToken' => self::CLIENT_TOKEN,
                    'is3dSecureEnabled' => true,
                    'useVault' => true,
                    'getCountrySpecificCardTypeConfig' => [
                        'US' => ['VI', 'AE', 'MA'],
                    ],
                    'isFraudDetectionEnabled' => true,
                    'isCcDetectionEnabled' => true,
                    'getBraintreeDataJs' => 'https://js.braintreegateway.com/v1/braintree-data.js'
                ],
                'vault_data' => [
                    'currentCustomerStoredCards' => [],
                ],
                'token_nonce_map' => [
                    ['token1', 'nonce1'],
                    ['token2', 'nonce2'],
                ],
                'expected_result' => [
                    'payment' => [
                        'braintree' => [
                            'clientToken' => self::CLIENT_TOKEN,
                            'useVault' => false,
                            'canSaveCard' => true,
                            'show3dSecure' => true,
                            'storedCards' => [
                            ],
                            'selectedCardToken' => null,
                            'creditCardExpMonth' => self::TODAY_MONTH,
                            'creditCardExpYear' => self::TODAY_YEAR,
                            'countrySpecificCardTypes' => [
                                'US' => ['VI', 'AE', 'MA'],
                            ],
                            'isFraudDetectionEnabled' => true,
                            'isCcDetectionEnabled' => true,
                            'availableCardTypes' => $this->availableCardTypes,
                            'braintreeDataJs'=> 'https://js.braintreegateway.com/v1/braintree-data.js',
                            'ajaxGenerateNonceUrl' => self::PAYMENT_NONCE_GENERATION_URL
                        ],
                    ],
                ]
            ],
        ];
    }
}
