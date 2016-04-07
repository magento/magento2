<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Source;

use Magento\Store\Model\Store;

/**
 * Unit test for Magento\Email\Model\Source\Variables
 */
class VariablesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Variables model
     *
     * @var \Magento\Email\Model\Source\Variables
     */
    protected $model;

    /**
     * Config variables
     *
     * @var array
     */
    protected $configVariables;

    protected function setup()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject('Magento\Email\Model\Source\Variables');
        $this->configVariables = [
            [
                'value' => Store::XML_PATH_UNSECURE_BASE_URL,
                'label' => __('Base Unsecure URL'),
            ],
            ['value' => Store::XML_PATH_SECURE_BASE_URL, 'label' => __('Base Secure URL')],
            ['value' => 'trans_email/ident_general/name', 'label' => __('General Contact Name')],
            ['value' => 'trans_email/ident_general/email', 'label' => __('General Contact Email')],
            ['value' => 'trans_email/ident_sales/name', 'label' => __('Sales Representative Contact Name')],
            ['value' => 'trans_email/ident_sales/email', 'label' => __('Sales Representative Contact Email')],
            ['value' => 'trans_email/ident_custom1/name', 'label' => __('Custom1 Contact Name')],
            ['value' => 'trans_email/ident_custom1/email', 'label' => __('Custom1 Contact Email')],
            ['value' => 'trans_email/ident_custom2/name', 'label' => __('Custom2 Contact Name')],
            ['value' => 'trans_email/ident_custom2/email', 'label' => __('Custom2 Contact Email')],
            ['value' => 'general/store_information/name', 'label' => __('Store Name')],
            ['value' => 'general/store_information/phone', 'label' => __('Store Phone Number')],
            ['value' => 'general/store_information/hours', 'label' => __('Store Hours')],
            ['value' => 'general/store_information/country_id', 'label' => __('Country')],
            ['value' => 'general/store_information/region_id', 'label' => __('Region/State')],
            ['value' => 'general/store_information/postcode', 'label' => __('Zip/Postal Code')],
            ['value' => 'general/store_information/city', 'label' => __('City')],
            ['value' => 'general/store_information/street_line1', 'label' => __('Street Address 1')],
            ['value' => 'general/store_information/street_line2', 'label' => __('Street Address 2')],
        ];
    }

    public function testToOptionArrayWithoutGroup()
    {
        $optionArray = $this->model->toOptionArray();
        $this->assertEquals(count($this->configVariables), count($optionArray));

        $index = 0;
        foreach ($optionArray as $variable) {
            $expectedValue = '{{config path="' . $this->configVariables[$index]['value'] . '"}}';
            $expectedLabel = $this->configVariables[$index]['label'];
            $this->assertEquals($expectedValue, $variable['value']);
            $this->assertEquals($expectedLabel, $variable['label']->getText());
            $index++;
        }
    }

    public function testToOptionArrayWithGroup()
    {
        $optionArray = $this->model->toOptionArray(true);
        $this->assertEquals('Store Contact Information', $optionArray['label']->getText());

        $optionArrayValues = $optionArray['value'];
        $this->assertEquals(count($this->configVariables), count($optionArrayValues));

        $index = 0;
        foreach ($optionArrayValues as $variable) {
            $expectedValue = '{{config path="' . $this->configVariables[$index]['value'] . '"}}';
            $expectedLabel = $this->configVariables[$index]['label'];
            $this->assertEquals($expectedValue, $variable['value']);
            $this->assertEquals($expectedLabel, $variable['label']->getText());
            $index++;
        }
    }
}
