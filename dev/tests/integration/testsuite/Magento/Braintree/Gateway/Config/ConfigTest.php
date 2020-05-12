<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Gateway\Config;

use Magento\Config\Model\Config as SystemConfig;
use Magento\TestFramework\Helper\Bootstrap;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    const METHOD_CODE = 'braintree';

    /** @var Config */
    private $config;

    /** @var SystemConfig */
    private $systemConfig;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->config = $objectManager->create(Config::class, [
            'methodCode' => self::METHOD_CODE
        ]);
        $this->systemConfig = $objectManager->create(SystemConfig::class);
    }

    /**
     * Test methods that load Braintree configuration, and verify that values were json decoded correctly
     * by the serializer dependency.
     *
     * @magentoDbIsolation enabled
     * @dataProvider countryCreditRetrievalProvider
     * @param string $value
     * @param array $expected
     */
    public function testCountryCreditRetrieval($value, array $expected)
    {
        $this->systemConfig->setDataByPath('payment/' . self::METHOD_CODE . '/countrycreditcard', $value);
        $this->systemConfig->save();

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
                'value' => '[]',
                'expected' => []
            ],
            'valid_data' => [
                'value' => '{"AF":["AE","VI"],"US":["AE","VI","MA"]}',
                'expected' => [
                    'AF' => ['AE', 'VI'],
                    'US' => ['AE', 'VI', 'MA']
                ]
            ]
        ];
    }
}
