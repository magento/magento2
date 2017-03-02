<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use \Magento\Framework\App\Area;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $_block;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $params = ['layout' => $objectManager->create(\Magento\Framework\View\Layout::class, [])];
        $context = $objectManager->create(\Magento\Framework\View\Element\Template\Context::class, $params);
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Framework\View\Element\Template::class,
            '',
            ['context' => $context]
        );
    }

    public function testConstruct()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Framework\View\Element\Template::class,
            '',
            ['data' => ['template' => 'value']]
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
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setAreaCode('frontend');
        $this->assertEquals('frontend', $this->_block->getArea());
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\State::class
        )->setAreaCode(Area::AREA_ADMINHTML);
        $this->assertEquals(Area::AREA_ADMINHTML, $this->_block->getArea());
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\State::class
        )->setAreaCode(Area::AREA_GLOBAL);
        $this->assertEquals(Area::AREA_GLOBAL, $this->_block->getArea());
    }

    /**
     * @covers \Magento\Framework\View\Element\AbstractBlock::toHtml
     * @see testAssign()
     */
    public function testToHtml()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setAreaCode(Area::AREA_GLOBAL);
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
        $object = new \Magento\Framework\DataObject(['key' => 'value']);
        $this->assertEquals('value', $this->_block->getObjectData($object, 'key'));
    }

    public function testGetCacheKeyInfo()
    {
        $this->assertArrayHasKey('template', $this->_block->getCacheKeyInfo());
    }
}
