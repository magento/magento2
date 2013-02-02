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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Block_TemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Block_Template
     */
    protected $_block;

    protected function setUp()
    {
        $params = array(
            'layout' => Mage::getObjectManager()->create('Mage_Core_Model_Layout', array(), false)
        );
        $this->_block = Mage::app()->getLayout()->createBlock('Mage_Core_Block_Template', '', $params);
    }

    protected function tearDown()
    {
        $this->_block = null;
    }

    public function testConstruct()
    {
        $block = Mage::app()->getLayout()->createBlock('Mage_Core_Block_Template', '',
            array('data' => array('template' => 'value'))
        );
        $this->assertEquals('value', $block->getTemplate());
    }

    public function testSetGetTemplate()
    {
        $this->assertEmpty($this->_block->getTemplate());
        $this->_block->setTemplate('value');
        $this->assertEquals('value', $this->_block->getTemplate());
    }

    public function testGetArea()
    {
        $this->assertEquals('frontend', $this->_block->getArea());
        $this->_block->setLayout(Mage::getModel('Mage_Core_Model_Layout', array('area' => 'some_area')));
        $this->assertEquals('some_area', $this->_block->getArea());
        $this->_block->setArea('another_area');
        $this->assertEquals('another_area', $this->_block->getArea());
    }

    public function testGetDirectOutput()
    {
        $this->assertFalse($this->_block->getDirectOutput());

        $layout = Mage::getModel('Mage_Core_Model_Layout');
        $layout->setDirectOutput(true);
        $this->_block->setLayout($layout);
        $this->assertTrue($this->_block->getDirectOutput());
    }

    public function testGetShowTemplateHints()
    {
        $this->assertFalse($this->_block->getShowTemplateHints());
    }

    /**
     * @covers Mage_Core_Block_Template::_toHtml
     * @covers Mage_Core_Block_Abstract::toHtml
     * @see testAssign()
     */
    public function testToHtml()
    {
        $this->assertEmpty($this->_block->toHtml());
        $this->_block->setTemplate(uniqid('invalid_filename.phtml'));
        $this->assertEmpty($this->_block->toHtml());
    }

    public function testGetBaseUrl()
    {
        $this->assertEquals('http://localhost/index.php/', $this->_block->getBaseUrl());
    }

    public function testGetObjectData()
    {
        $object = new Varien_Object(array('key' => 'value'));
        $this->assertEquals('value', $this->_block->getObjectData($object, 'key'));
    }

    public function testGetCacheKeyInfo()
    {
        $this->assertArrayHasKey('template', $this->_block->getCacheKeyInfo());
    }
}
