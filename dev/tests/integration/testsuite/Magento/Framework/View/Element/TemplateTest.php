<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\App\Area;

class TemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $_block;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    private $origMode;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $params = ['layout' => $this->objectManager->create(\Magento\Framework\View\Layout::class, [])];
        $context = $this->objectManager->create(\Magento\Framework\View\Element\Template\Context::class, $params);
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Framework\View\Element\Template::class,
            '',
            ['context' => $context]
        );

        /** @var \Magento\TestFramework\App\State $appState */
        $appState = $this->objectManager->get(\Magento\TestFramework\App\State::class);
        $this->origMode = $appState->getMode();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        /** @var \Magento\TestFramework\App\State $appState */
        $appState = $this->objectManager->get(\Magento\TestFramework\App\State::class);
        $appState->setMode($this->origMode);
        parent::tearDown();
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
        /** @var \Magento\TestFramework\App\State $appState */
        $appState = $this->objectManager->get(\Magento\TestFramework\App\State::class);
        $appState->setMode(\Magento\TestFramework\App\State::MODE_DEFAULT);
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
