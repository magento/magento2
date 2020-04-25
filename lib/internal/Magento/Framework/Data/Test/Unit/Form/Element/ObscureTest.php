<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Obscure;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
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

    protected function setUp(): void
    {
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
