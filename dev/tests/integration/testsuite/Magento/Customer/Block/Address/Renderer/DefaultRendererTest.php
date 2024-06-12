<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Address\Renderer;

use Magento\Eav\Model\AttributeDataFactory;

/**
 * DefaultRenderer
 */
class DefaultRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    protected function setUp(): void
    {
        $this->_addressConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Customer\Model\Address\Config::class
        );
    }

    /**
     * @dataProvider renderArrayDataProvider
     */
    public function testRenderArray($addressAttributes, $format, $expected)
    {
        /** @var DefaultRenderer $renderer */
        $renderer = $this->_addressConfig->getFormatByCode($format)->getRenderer();
        $actual = $renderer->renderArray($addressAttributes);
        $this->assertEquals($expected, $actual);
    }

    public static function renderArrayDataProvider()
    {
        $addressAttributes = [
            'city' => 'CityM',
            'country_id' => 'US',
            'firstname' => 'John',
            'lastname' => 'Smith',
            'postcode' => '75477',
            'region' => 'Alabama',
            'region_id' => '1',
            'street' => ['Green str, 67'],
            'telephone' => '3468676',
        ];

        $htmlResult = "John Smith<br />\n\nGreen str, 67<br />\n\n\n\nCityM,  Alabama, " .
            "75477<br />\nUnited States<br />\nT: <a href=\"tel:3468676\">3468676</a>\n\n";
        return [
            [$addressAttributes, AttributeDataFactory::OUTPUT_FORMAT_HTML, $htmlResult],
            [
                $addressAttributes,
                AttributeDataFactory::OUTPUT_FORMAT_PDF,
                "John Smith|\n\nGreen str, 67|\n\n\n\nCityM, Alabama, 75477|\nUnited States|\nT: 3468676|\n|\n|"
            ],
            [
                $addressAttributes,
                AttributeDataFactory::OUTPUT_FORMAT_ONELINE,
                "John Smith, Green str, 67, CityM, Alabama 75477, United States"
            ],
            [
                $addressAttributes,
                AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                "John Smith\n\nGreen str, 67\n\n\n\n\nCityM,  Alabama, 75477\nUnited States\nT: 3468676\n\n"
            ]
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender($address, $format, $expected)
    {
        /** @var DefaultRenderer $renderer */
        $renderer = $this->_addressConfig->getFormatByCode($format)->getRenderer();
        $actual = $renderer->render($address);
        $this->assertEquals($expected, $actual);
    }

    public static function renderDataProvider()
    {
        $data = [
            'city' => 'CityM',
            'country_id' => 'US',
            'firstname' => 'John',
            'lastname' => 'Smith',
            'postcode' => '75477',
            'region' => 'Alabama',
            'region_id' => '1',
            'street' => ['Green str, 67'],
            'telephone' => '3468676',
        ];

        $address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Address::class
        )->setData(
            $data
        );

        return [
            [
                $address,
                AttributeDataFactory::OUTPUT_FORMAT_HTML,
                "John Smith<br />\n\nGreen str, 67<br />\n\n\n\nCityM,  Alabama, 75477<br />
United States<br />\nT: <a href=\"tel:3468676\">3468676</a>\n\n",
            ],
            [
                $address,
                AttributeDataFactory::OUTPUT_FORMAT_PDF,
                "John Smith|\n\nGreen str, 67|\n\n\n\nCityM, Alabama, 75477|
United States|\nT: 3468676|\n|\n|"
            ],
            [
                $address,
                AttributeDataFactory::OUTPUT_FORMAT_ONELINE,
                "John Smith, Green str, 67, CityM, Alabama 75477, United States"
            ],
            [
                $address,
                AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                "John Smith\n\nGreen str, 67\n\n\n\n\nCityM,  Alabama, 75477
United States\nT: 3468676\n\n"
            ]
        ];
    }
}
