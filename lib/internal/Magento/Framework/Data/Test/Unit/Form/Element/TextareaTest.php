<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Textarea;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Magento\Framework\Data\Form\Element\Textarea class.
 */
class TextareaTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var Textarea
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
        $this->_model = new Textarea(
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
     * @covers \Magento\Framework\Data\Form\Element\Textarea::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('textarea', $this->_model->getType());
        $this->assertEquals('textarea', $this->_model->getExtType());
        $this->assertEquals(2, $this->_model->getRows());
        $this->assertEquals(15, $this->_model->getCols());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Textarea::getElementHtml
     */
    public function testGetElementHtml()
    {
        $html = $this->_model->getElementHtml();
        $this->assertStringContainsString('</textarea>', $html);
        $this->assertStringContainsString('rows="2"', $html);
        $this->assertStringContainsString('cols="15"', $html);
        $this->assertGreaterThan(0, preg_match('/class=\".*textarea.*\"/i', $html));
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Textarea::getHtmlAttributes
     */
    public function testGetHtmlAttributes()
    {
        $this->assertEmpty(
            array_diff(
                [
                    'title',
                    'class',
                    'style',
                    'onclick',
                    'onchange',
                    'rows',
                    'cols',
                    'readonly',
                    'maxlength',
                    'disabled',
                    'onkeyup',
                    'tabindex',
                ],
                $this->_model->getHtmlAttributes()
            )
        );
    }
}
