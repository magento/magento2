<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Element\Hidden
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Hidden;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HiddenTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var Hidden
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|null
     */
    private $originalObjectManager;

    protected function setUp(): void
    {
        // Configure ObjectManager mock to provide Random and SecureHtmlRenderer
        // for AbstractElement parent constructor
        try {
            $this->originalObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        } catch (\RuntimeException $e) {
            $this->originalObjectManager = null;
        }

        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('some-rando-string');

        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderEventListenerAsTag')->willReturn('');
        $secureRendererMock->method('renderTag')->willReturn('');

        $objectManagerMock = $this->createMock(\Magento\Framework\App\ObjectManager::class);
        $objectManagerMock->method('get')
            ->willReturnCallback(function ($className) use ($randomMock, $secureRendererMock) {
                if ($className === Random::class) {
                    return $randomMock;
                }
                if ($className === SecureHtmlRenderer::class) {
                    return $secureRendererMock;
                }
                return null;
            });
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        $factoryMock = $this->createMock(Factory::class);
        $collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $escaperMock = $this->createMock(Escaper::class);
        $this->_model = new Hidden(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock
        );
        $formMock = new DataObject();
        $formMock->getHtmlIdPrefix('id_prefix');
        $formMock->getHtmlIdPrefix('id_suffix');
        $this->_model->setForm($formMock);
    }

    protected function tearDown(): void
    {
        // Restore original ObjectManager instance
        if ($this->originalObjectManager) {
            \Magento\Framework\App\ObjectManager::setInstance($this->originalObjectManager);
        }
        parent::tearDown();
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Hidden::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('hidden', $this->_model->getType());
        $this->assertEquals('hiddenfield', $this->_model->getExtType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Hidden::getDefaultHtml
     */
    public function testGetDefaultHtml()
    {
        $html = $this->_model->getDefaultHtml();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="hidden"', $html);
        $this->_model->setDefaultHtml('testhtml');
        $this->assertEquals('testhtml', $this->_model->getDefaultHtml());
    }
}
