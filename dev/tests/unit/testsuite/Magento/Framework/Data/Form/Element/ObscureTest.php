<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Obscure
 */
namespace Magento\Framework\Data\Form\Element;

class ObscureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\Obscure
     */
    protected $_model;

    protected function setUp()
    {
        $factoryMock = $this->getMock('\Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $collectionFactoryMock = $this->getMock(
            '\Magento\Framework\Data\Form\Element\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $escaperMock = $this->getMock('\Magento\Framework\Escaper', [], [], '', false);
        $this->_model = new \Magento\Framework\Data\Form\Element\Obscure(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock
        );
        $formMock = new \Magento\Framework\Object();
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
        $this->assertContains('value="******"', $this->_model->getElementHtml());
        $this->_model->setValue('');
        $this->assertContains('value=""', $this->_model->getElementHtml());
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
