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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Core
 */
class Mage_Core_Helper_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Helper_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper = null;

    protected function setUp()
    {
        $this->_helper = $this->getMock('Mage_Core_Helper_Abstract', array('_getModuleName'));
        $this->_helper
            ->expects($this->any())
            ->method('_getModuleName')
            ->will($this->returnValue('Mage_Core'))
        ;
    }

    /**
     * @covers Mage_Core_Helper_Abstract::isModuleEnabled
     * @covers Mage_Core_Helper_Abstract::isModuleOutputEnabled
     */
    public function testIsModuleEnabled()
    {
        $this->assertTrue($this->_helper->isModuleEnabled());
        $this->assertTrue($this->_helper->isModuleOutputEnabled());
    }

    public function test__()
    {
        $uniqueText = uniqid('prefix_');
        $this->assertEquals($uniqueText, $this->_helper->__($uniqueText));
    }

    /**
     * @dataProvider escapeHtmlDataProvider
     */
    public function testEscapeHtml($data, $expected)
    {
        $actual = $this->_helper->escapeHtml($data);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function escapeHtmlDataProvider()
    {
        return array(
            'array data' => array(
                'data' => array('one', '<two>three</two>'),
                'expected' => array('one', '&lt;two&gt;three&lt;/two&gt;')
            ),
            'string data conversion' => array(
                'data' => '<two>three</two>',
                'expected' => '&lt;two&gt;three&lt;/two&gt;'
            ),
            'string data no conversion' => array(
                'data' => 'one',
                'expected' => 'one'
            )
        );
    }

    public function testStripTags()
    {
        $this->assertEquals('three', $this->_helper->stripTags('<two>three</two>'));
    }

    /**
     * @covers Mage_Core_Helper_Abstract::escapeUrl
     */
    public function testEscapeUrl()
    {
        $data = '<two>"three</two>';
        $expected = '&lt;two&gt;&quot;three&lt;/two&gt;';
        $this->assertEquals($expected, $this->_helper->escapeUrl($data));
    }

    public function testJsQuoteEscape()
    {
        $data = array("Don't do that.", 'lost_key' => "Can't do that.");
        $expected = array("Don\\'t do that.", "Can\\'t do that.");
        $this->assertEquals($expected, $this->_helper->jsQuoteEscape($data));
        $this->assertEquals($expected[0], $this->_helper->jsQuoteEscape($data[0]));
    }

    /**
     * @covers Mage_Core_Helper_Abstract::quoteEscape
     */
    public function testQuoteEscape()
    {
        $data = "Text with 'single' and \"double\" quotes";
        $expected = array(
            "Text with &#039;single&#039; and &quot;double&quot; quotes",
            "Text with \\&#039;single\\&#039; and \\&quot;double\\&quot; quotes",
        );
        $this->assertEquals($expected[0], $this->_helper->quoteEscape($data));
        $this->assertEquals($expected[1], $this->_helper->quoteEscape($data, true));
    }

    public function testSetGetLayout()
    {
        $this->assertNull($this->_helper->getLayout());
        $this->assertInstanceof(get_class($this->_helper), $this->_helper->setLayout(Mage::app()->getLayout()));
        $this->assertInstanceOf('Mage_Core_Model_Layout', $this->_helper->getLayout());
    }

    public function testUrlEncodeDecode()
    {
        $data = uniqid();
        $result = $this->_helper->urlEncode($data);
        $this->assertNotContains('&', $result);
        $this->assertNotContains('%', $result);
        $this->assertNotContains('+', $result);
        $this->assertNotContains('=', $result);
        $this->assertEquals($data, $this->_helper->urlDecode($result));
    }

    public function testTranslateArray()
    {
        $data = array(uniqid(), array(uniqid(), array(uniqid())));
        $this->assertEquals($data, $this->_helper->translateArray($data));
    }
}
