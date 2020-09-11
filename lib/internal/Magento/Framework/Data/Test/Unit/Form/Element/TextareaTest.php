<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Textarea;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
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

    protected function setUp(): void
    {
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
