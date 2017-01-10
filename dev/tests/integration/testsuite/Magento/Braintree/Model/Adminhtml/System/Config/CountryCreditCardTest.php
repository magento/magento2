<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Model\Adminhtml\System\Config;

use Magento\Braintree\Model\Adminhtml\System\Config\CountryCreditCard;
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
        $loadedIndexedArray = $this->replaceHashes($loadedHashedArray);
        $this->assertEquals($value, $loadedIndexedArray);
    }

    /**
     * Simple function that will replace random hashes at the root level with indices
     *
     * @param  $loadedHashedArray
     * @return array
     */
    private function replaceHashes($loadedHashedArray)
    {
        $returnArray = [];
        foreach ($loadedHashedArray as $creditCardConfig) {
            $returnArray[] = $creditCardConfig;
        }

        return $returnArray;
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
