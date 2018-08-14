<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Checkbox
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

class CheckboxTest extends \PHPUnit\Framework\TestCase
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
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
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
