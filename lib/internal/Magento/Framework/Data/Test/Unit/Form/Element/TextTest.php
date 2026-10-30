<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Element\Text
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var Text
     */
    protected $_model;

        /**
     * @var \Magento\Framework\ObjectManagerInterface|null
     */
    private $originalObjectManager;

    protected function setUp(): void
    {
        // Configure ObjectManager mock for AbstractElement parent constructor
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
        $this->_model = new Text(
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
        if ($this->originalObjectManager) {
            \Magento\Framework\App\ObjectManager::setInstance($this->originalObjectManager);
        }
        parent::tearDown();
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Text::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('text', $this->_model->getType());
        $this->assertEquals('textfield', $this->_model->getExtType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Text::getHtml
     */
    public function testGetHtml()
    {
        $html = $this->_model->getHtml();
        $this->assertStringContainsString('type="text"', $html);
        $this->assertGreaterThan(0, preg_match('/class=\".*input-text.*\"/i', $html));
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Text::getHtmlAttributes
     */
    public function testGetHtmlAttributes()
    {
        $this->assertEmpty(
            array_diff(
                [
                    'type',
                    'title',
                    'class',
                    'style',
                    'onclick',
                    'onchange',
                    'onkeyup',
                    'disabled',
                    'readonly',
                    'maxlength',
                    'tabindex',
                    'placeholder',
                ],
                $this->_model->getHtmlAttributes()
            )
        );
    }
}
