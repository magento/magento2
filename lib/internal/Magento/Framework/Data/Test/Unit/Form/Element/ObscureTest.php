<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Obscure;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Magento\Framework\Data\Form\Element\Obscure
 */
class ObscureTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @var Obscure
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
        $this->objectManager = new ObjectManager($this);
        $escaper = $this->objectManager->getObject(
            Escaper::class
        );
        $this->_model = new Obscure(
            $factoryMock,
            $collectionFactoryMock,
            $escaper
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
     * @covers \Magento\Framework\Data\Form\Element\Obscure::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('password', $this->_model->getType());
        $this->assertEquals('textfield', $this->_model->getExtType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Obscure::getEscapedValue
     */
    public function testGetEscapedValue()
    {
        $this->_model->setValue('Obscure Text');
        $this->assertStringContainsString('value="******"', $this->_model->getElementHtml());
        $this->_model->setValue('');
        $this->assertStringContainsString('value=""', $this->_model->getElementHtml());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Obscure::getHtmlAttributes
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
                ],
                $this->_model->getHtmlAttributes()
            )
        );
    }
}
