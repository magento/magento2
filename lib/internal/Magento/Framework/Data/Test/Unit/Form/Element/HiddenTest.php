<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Hidden
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

class HiddenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\Hidden
     */
    protected $_model;

    protected function setUp()
    {
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->_model = new \Magento\Framework\Data\Form\Element\Hidden(
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
     * @covers \Magento\Framework\Data\Form\Element\Hidden::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('hidden', $this->_model->getType());
        $this->assertEquals('hiddenfield', $this->_model->getExtType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Hidden::getDefaultHtml
     */
    public function testGetDefaultHtml()
    {
        $html = $this->_model->getDefaultHtml();
        $this->assertContains('<input', $html);
        $this->assertContains('type="hidden"', $html);
        $this->_model->setDefaultHtml('testhtml');
        $this->assertEquals('testhtml', $this->_model->getDefaultHtml());
    }
}
