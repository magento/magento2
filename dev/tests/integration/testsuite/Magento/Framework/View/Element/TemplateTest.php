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
namespace Magento\Framework\View\Element;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $_block;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $params = array('layout' => $objectManager->create('Magento\Framework\View\Layout', array()));
        $context = $objectManager->create('Magento\Framework\View\Element\Template\Context', $params);
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Framework\View\Element\Template',
            '',
            array('context' => $context, 'data' => array('module_name' => 'Magento_View'))
        );
    }

    public function testConstruct()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Framework\View\Element\Template',
            '',
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
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $this->assertEquals('frontend', $this->_block->getArea());
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\State'
        )->setAreaCode(
            'some_area'
        );
        $this->assertEquals('some_area', $this->_block->getArea());
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\State'
        )->setAreaCode(
            'another_area'
        );
        $this->assertEquals('another_area', $this->_block->getArea());
    }

    /**
     * @covers \Magento\Framework\View\Element\AbstractBlock::toHtml
     * @see testAssign()
     */
    public function testToHtml()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('any area');
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
        $object = new \Magento\Framework\Object(array('key' => 'value'));
        $this->assertEquals('value', $this->_block->getObjectData($object, 'key'));
    }

    public function testGetCacheKeyInfo()
    {
        $this->assertArrayHasKey('template', $this->_block->getCacheKeyInfo());
    }
}
