<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Hidden
 */
namespace Magento\Framework\Data\Form\Element;

class HiddenTest extends \PHPUnit_Framework_TestCase
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
        $factoryMock = $this->getMock('\Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $collectionFactoryMock = $this->getMock(
            '\Magento\Framework\Data\Form\Element\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $escaperMock = $this->getMock('\Magento\Framework\Escaper', [], [], '', false);
        $this->_model = new \Magento\Framework\Data\Form\Element\Hidden(
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
