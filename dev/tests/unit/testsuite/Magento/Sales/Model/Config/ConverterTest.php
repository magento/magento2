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
namespace Magento\Sales\Model\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Config\Converter
     */
    protected $_converter;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->_converter = new \Magento\Sales\Model\Config\Converter();
    }

    /**
     * Testing wrong data incoming
     */
    public function testConvertWrongIncomingData()
    {
        $result = $this->_converter->convert(array('wrong data'));
        $this->assertEmpty($result);
    }

    /**
     * Testing empty data
     */
    public function testConvertNoElements()
    {
        $result = $this->_converter->convert(new \DOMDocument());
        $this->assertEmpty($result);
    }

    /**
     * Testing converting valid cron configuration
     */
    public function testConvert()
    {
        $expected = array(
            'section1' => array(
                'group1' => array(
                    'item1' => array(
                        'instance' => 'instance1',
                        'sort_order' => '1',
                        'renderers' => array('renderer1' => 'instance1')
                    )
                ),
                'group2' => array(
                    'item1' => array('instance' => 'instance1', 'sort_order' => '1', 'renderers' => array())
                )
            ),
            'section2' => array(
                'group1' => array(
                    'item1' => array('instance' => 'instance1', 'sort_order' => '1', 'renderers' => array())
                )
            ),
            'order' => array('available_product_types' => array('type1', 'type2'))
        );

        $xmlFile = __DIR__ . '/_files/sales_valid.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->_converter->convert($dom);
        $this->assertEquals($expected, $result);
    }

    /**
     * Testing converting not valid cron configuration, expect to get exception
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConvertWrongConfiguration()
    {
        $xmlFile = __DIR__ . '/_files/sales_invalid.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $this->_converter->convert($dom);
    }
}
