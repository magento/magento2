<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Checkbox
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

class CheckboxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\Checkbox
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
        $this->_model = new \Magento\Framework\Data\Form\Element\Checkbox(
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
     * @covers \Magento\Framework\Data\Form\Element\Checkbox::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('checkbox', $this->_model->getType());
        $this->assertEquals('checkbox', $this->_model->getExtType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Checkbox::getHtmlAttributes
     */
    public function testGetHtmlAttributes()
    {
        $this->assertEmpty(
            array_diff(
                ['type', 'title', 'class', 'style', 'checked', 'onclick', 'onchange', 'disabled', 'tabindex'],
                $this->_model->getHtmlAttributes()
            )
        );
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Checkbox::getElementHtml
     */
    public function testGetElementHtml()
    {
        $this->_model->setIsChecked(false);
        $html = $this->_model->getElementHtml();
        $this->assertContains('type="checkbox"', $html);
        $this->assertNotContains('checked="checked"', $html);
        $this->_model->setIsChecked(true);
        $html = $this->_model->getElementHtml();
        $this->assertContains('checked="checked"', $html);
    }
}
