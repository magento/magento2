<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Gateway\Config;

use Magento\Braintree\Model\Adminhtml\System\Config\CountryCreditCard;
use Magento\TestFramework\Helper\Bootstrap;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    const METHOD_CODE = 'braintree';

    /** @var Config */
    private $config;

    /** @var CountryCreditCard */
    private $countryCreditCardConfig;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->config = $objectManager->create(Config::class, [
            'methodCode' => self::METHOD_CODE
        ]);
        $this->countryCreditCardConfig = $objectManager->create(CountryCreditCard::class);
        $this->countryCreditCardConfig->setPath('payment/braintree/countrycreditcard');
    }

    /**
     * Test methods that load Braintree configuration, and verify that values were json decoded correctly
     * by the serializer dependency.
     *
     * @magentoDbIsolation enabled
     * @dataProvider countryCreditRetrievalProvider
     * @param array $value
     * @param array $expected
     */
    public function testCountryCreditRetrieval(array $value, array $expected)
    {
        $this->countryCreditCardConfig->setValue($value);
        $this->countryCreditCardConfig->save();

        $countrySpecificCardTypeConfig = $this->config->getCountrySpecificCardTypeConfig();
        $this->assertEquals($expected, $countrySpecificCardTypeConfig);

        foreach ($expected as $country => $expectedCreditCardTypes) {
            $countryAvailableCardTypes = $this->config->getCountryAvailableCardTypes($country);
            $this->assertEquals($expectedCreditCardTypes, $countryAvailableCardTypes);
        }
    }

    public function countryCreditRetrievalProvider()
    {
        return [
            'empty_array' => [
                'value' => [],
                'expected' => []
            ],
            'valid_data' => [
                'value' => [
                    [
                        'country_id' => 'AF',
                        'cc_types' => ['AE', 'VI']
                    ],
                    [
                        'country_id' => 'US',
                        'cc_types' => ['AE', 'VI', 'MA']
                    ]
                ],
                'expected' => [
                    'AF' => ['AE', 'VI'],
                    'US' => ['AE', 'VI', 'MA']
                ]
            ]
        ];
    }
}
