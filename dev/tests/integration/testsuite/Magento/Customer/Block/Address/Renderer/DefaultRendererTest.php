<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Block\Address\Renderer;

use Magento\Eav\Model\AttributeDataFactory;

/**
 * DefaultRenderer
 */
class DefaultRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    public function setUp()
    {
        $this->_addressConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Customer\Model\Address\Config');
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender($addressAttributes, $format, $expected)
    {
        /** @var DefaultRenderer $renderer */
        $renderer = $this->_addressConfig->getFormatByCode($format)->getRenderer();
        $actual = $renderer->renderArray($addressAttributes);
        $this->assertEquals($expected, $actual);
    }

    public function renderDataProvider()
    {
        $addressAttributes = [
            'city' => 'CityM',
            'country_id' => 'US',
            'firstname' => 'John',
            'lastname' => 'Smith',
            'postcode' => '75477',
            'region' => [
                'region' => 'Alabama',
                'region_id' => '1',
            ],
            'street' => ['Green str, 67'],
            'telephone' => '3468676',
        ];

        return [
            [
                $addressAttributes,
                AttributeDataFactory::OUTPUT_FORMAT_HTML,
                "John Smith<br/>\n\nGreen str, 67<br />\n\n\n\nCityM,  Alabama, 75477<br/>\n<br/>\nT: 3468676\n\n"
            ],
            [
                $addressAttributes,
                AttributeDataFactory::OUTPUT_FORMAT_PDF,
                "John Smith|\n\nGreen str, 67\n\n\n\n\nCityM,|\nAlabama, 75477|\n|\nT: 3468676|\n|\n|"
            ],
            [
                $addressAttributes,
                AttributeDataFactory::OUTPUT_FORMAT_ONELINE,
                "John Smith, Green str, 67, CityM, Alabama 75477, "
            ],
            [
                $addressAttributes,
                AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                "John Smith\n\nGreen str, 67\n\n\n\n\nCityM,  Alabama, 75477\n\nT: 3468676\n\n"
            ],
        ];
    }
}
