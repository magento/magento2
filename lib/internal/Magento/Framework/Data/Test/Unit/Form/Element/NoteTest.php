<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Note
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

class NoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\Note
     */
    protected $_model;

    protected function setUp()
    {
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $escaperMock->method('escapeHtml')->willReturnArgument(0);
        $this->_model = new \Magento\Framework\Data\Form\Element\Note(
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
     * @covers \Magento\Framework\Data\Form\Element\Note::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('note', $this->_model->getType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Note::getElementHtml
     */
    public function testGetElementHtml()
    {
        $this->_model->setBeforeElementHtml('note_before');
        $this->_model->setAfterElementHtml('note_after');
        $this->_model->setId('note_id');
        $this->_model->setData('ui_id', 'ui_id');
        $this->_model->setValue('Note Text');
        $html = $this->_model->getElementHtml();
        $this->assertEquals(
            "note_before<div id=\"note_id\" class=\"control-value admin__field-value\"></div>note_after",
            $html
        );
    }
}
