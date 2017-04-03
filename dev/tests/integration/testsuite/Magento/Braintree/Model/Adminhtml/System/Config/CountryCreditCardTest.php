<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Model\Adminhtml\System\Config;

use Magento\TestFramework\Helper\Bootstrap;

class CountryCreditCardTest extends \PHPUnit_Framework_TestCase
{
    /** @var CountryCreditCard */
    private $countryCreditCardConfig;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->countryCreditCardConfig = $objectManager->get(CountryCreditCard::class);
        $this->countryCreditCardConfig->setPath('payment/braintree/countrycreditcard');
    }

    /**
     * Test save and load lifecycle of the Braintree configuration value. Save should trigger the passed
     * array to be json encoded by the serializer. Load should trigger json decode of that value, and it
     * should match what was originally passed in.
     *
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @dataProvider saveAndLoadDataProvider
     * @param array $value
     * @param string $encodedExpectedValue
     */
    public function testSaveAndLoad($value, $encodedExpectedValue)
    {
        $this->countryCreditCardConfig->setValue($value);
        $this->countryCreditCardConfig->save();
        $this->assertEquals($encodedExpectedValue, $this->countryCreditCardConfig->getValue());

        $this->countryCreditCardConfig->load($this->countryCreditCardConfig->getId());
        $loadedHashedArray = $this->countryCreditCardConfig->getValue();
        // strip the random hashes added by routine before assertion
        $loadedIndexedArray = array_values($loadedHashedArray);
        $this->assertEquals($value, $loadedIndexedArray);
    }

    public function saveAndLoadDataProvider()
    {
        return [
            'empty_array' => [
                'value' => [],
                'expected' => '[]'
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
                'expected' => '{"AF":["AE","VI"],"US":["AE","VI","MA"]}'
            ]
        ];
    }
}
