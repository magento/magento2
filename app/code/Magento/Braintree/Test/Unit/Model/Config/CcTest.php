<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model\Config;

use Magento\Braintree\Model\Config;
use Magento\Braintree\Model\Config\Cc;
use Magento\Store\Model\ScopeInterface;
use Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CcTest
 *
 */
class CcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreeConfigurationMock;

    /**
     * @var \Magento\Framework\DB\TransactionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreeClientTokenMock;

    /**
     * @var \Magento\Braintree\Model\System\Config\Source\Country|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceCountryMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->braintreeConfigurationMock = $this->getMockBuilder(
            '\Magento\Braintree\Model\Adapter\BraintreeConfiguration'
        )->disableOriginalConstructor()
            ->getMock();
        $this->sourceCountryMock = $this->getMockBuilder('\Magento\Braintree\Model\System\Config\Source\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeClientTokenMock = $this->getMockBuilder('\Magento\Braintree\Model\Adapter\BraintreeClientToken')
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            '\Magento\Braintree\Model\Config\Cc',
            [
                'scopeConfig' => $this->scopeConfigMock,
                'braintreeConfiguration' => $this->braintreeConfigurationMock,
                'braintreeClientToken' => $this->braintreeClientTokenMock,
                'sourceCountry' => $this->sourceCountryMock,
            ]
        );
    }

    /**
     * @dataProvider canUseCcTypeForCountryDataProvider
     */
    public function testCanUseCcTypeForCountry($country, $ccType, $countryCardType, $ccTypes, $expectedResult)
    {
        $prefix = 'payment/braintree/';

        $valueMap = [
            [$prefix . Cc::KEY_COUNTRY_CREDIT_CARD, ScopeInterface::SCOPE_STORE, null, $countryCardType],
            [$prefix . Cc::KEY_CC_TYPES, ScopeInterface::SCOPE_STORE, null, $ccTypes],
        ];
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap($valueMap);

        $this->assertEquals($expectedResult, $this->model->canUseCcTypeForCountry($country, $ccType));
    }

    public function canUseCcTypeForCountryDataProvider()
    {
        return [
            'no_country_card_config' => [
                'country' => 'US',
                'cc_type' => 'VI',
                'country_specific_card_types' => 'illega_serialized_form',
                'cc_types' => 'VI,MA,AE',
                'expected_result' => false,
            ],
            'no_country_card_config_not_allowed' => [
                'country' => 'US',
                'cc_type' => 'random_card_type',
                'country_specific_card_types' => 'illega_serialized_form',
                'cc_types' => 'VI,MA,AE',
                'expected_result' => new \Magento\Framework\Phrase(
                    'Credit card type is not allowed for this payment method.'
                ),
            ],
            'country_card_country_not_found_card_type_not_allowed' => [
                'country' => 'US',
                'cc_type' => 'random_card_type',
                'country_specific_card_types' => serialize(
                    [
                        'FR' => ['VI', 'MA'],
                    ]
                ),
                'cc_types' => 'VI,MA,AE',
                'expected_result' => new \Magento\Framework\Phrase(
                    'Credit card type is not allowed for this payment method.'
                ),
            ],
            'country_card_not_found_card_type_not_allowed' => [
                'country' => 'US',
                'cc_type' => 'random_card_type',
                'country_specific_card_types' => serialize(
                    [
                        'US' => ['AE', 'MA'],
                    ]
                ),
                'cc_types' => 'VI,MA,AE',
                'expected_result' => new \Magento\Framework\Phrase(
                    'Credit card type is not allowed for your country.'
                ),
            ],
            'country_card_found' => [
                'country' => 'US',
                'cc_type' => 'random_card_type',
                'country_specific_card_types' => serialize(
                    [
                        'US' => ['AE', 'VI'],
                    ]
                ),
                'cc_types' => '',
                'expected_result' => new \Magento\Framework\Phrase(
                    'Credit card type is not allowed for your country.'
                ),
            ],
        ];
    }

    /**
     * @dataProvider getApplicableCardTypesDataProvider
     */
    public function testGetApplicableCardTypes($country, $countryCardType, $ccTypes, $expectedResult)
    {
        $prefix = 'payment/braintree/';

        $valueMap = [
            [$prefix . Cc::KEY_COUNTRY_CREDIT_CARD, ScopeInterface::SCOPE_STORE, null, $countryCardType],
            [$prefix . Cc::KEY_CC_TYPES, ScopeInterface::SCOPE_STORE, null, $ccTypes],
        ];
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap($valueMap);

        $this->assertEquals($expectedResult, $this->model->getApplicableCardTypes($country));
    }
    public function getApplicableCardTypesDataProvider()
    {
        return [
            'no_country_card_config' => [
                'country' => 'US',
                'country_specific_card_types' => 'illega_serialized_form',
                'cc_types' => 'VI,MA,AE',
                'expected_result' => ['VI', 'MA', 'AE'],
            ],
            'country_card_country_not_found_card_type_not_allowed' => [
                'country' => 'US',
                'country_specific_card_types' => serialize(
                    [
                        'FR' => ['VI', 'MA', 'DI'],
                    ]
                ),
                'cc_types' => 'VI,MA,AE',
                'expected_result' => ['VI', 'MA', 'AE'],
            ],
            'country_card_not_found_card_type_not_allowed' => [
                'country' => 'US',
                'country_specific_card_types' => serialize(
                    [
                        'US' => ['AE', 'MA'],
                    ]
                ),
                'cc_types' => 'VI,MA,AE',
                'expected_result' => ['AE', 'MA'],
            ],
        ];
    }

    /**
     * @dataProvider getCountrySpecificCardTypeConfigDataProvider
     */
    public function testGetCountrySpecificCardTypeConfig($countryCardType, $expectedResult)
    {
        $prefix = 'payment/braintree/';

        $valueMap = [
            [$prefix . Cc::KEY_COUNTRY_CREDIT_CARD, ScopeInterface::SCOPE_STORE, null, $countryCardType],
        ];
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap($valueMap);

        $this->assertEquals($expectedResult, $this->model->getCountrySpecificCardTypeConfig());
    }

    public function getCountrySpecificCardTypeConfigDataProvider()
    {
        return [
            'no_country_card_config' => [
                'country_specific_card_types' => 'illega_serialized_form',
                'expected' => [],
            ],
            'country_card_country_not_found_card_type_not_allowed' => [
                'country_specific_card_types' => serialize(
                    [
                        'US' => ['VI', 'MA', 'AE'],
                        'FR' => ['VI', 'MA', 'DI'],
                    ]
                ),
                'expected_result' => [
                    'US' => ['VI', 'MA', 'AE'],
                    'FR' => ['VI', 'MA', 'DI'],
                ],
            ],

        ];
    }
}
