<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Test\Unit\Form\Element;

/**
 * Tests for \Magento\Framework\Data\Form\Element\Textarea class.
 */
class TextareaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\Textarea
     */
    protected $_model;

    protected function setUp(): void
    {
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->_model = new \Magento\Framework\Data\Form\Element\Textarea(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock
        );
        $formMock = new \Magento\Framework\DataObject();
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
        $this->assertTrue(preg_match('/class=\".*textarea.*\"/i', $html) > 0);
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
