<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\Config\Converter
     */
    protected $_converter;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->_converter = new \Magento\Framework\Api\Config\Converter();
    }

    /**
     * Test invalid data
     */
    public function testInvalidData()
    {
        $result = $this->_converter->convert(['invalid data']);
        $this->assertEmpty($result);
    }

    /**
     * Test empty data
     */
    public function testConvertNoElements()
    {
        $result = $this->_converter->convert(new \DOMDocument());
        $this->assertEmpty($result);
    }

    /**
     * Test converting valid data object config
     */
    public function testConvert()
    {
        $expected = [
            'Magento\Tax\Api\Data\TaxRateInterface' => [
            ],
            'Magento\Catalog\Api\Data\ProductInterface' => [
                'stock_item' => 'Magento\CatalogInventory\Api\Data\StockItemInterface'
            ],
            'Magento\Customer\Api\Data\CustomerInterface' => [
                'custom_1' => 'Magento\Customer\Api\Data\CustomerCustom',
                'custom_2' => 'Magento\CustomerExtra\Api\Data\CustomerCustom2'
            ],
        ];

        $xmlFile = __DIR__ . '/_files/data_object_valid.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->_converter->convert($dom);
        $this->assertEquals($expected, $result);
    }
}
