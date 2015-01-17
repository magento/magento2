<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Label
 */
namespace Magento\Framework\Data\Form\Element;

class LabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\Label
     */
    protected $_label;

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
        $this->_label = new \Magento\Framework\Data\Form\Element\Label(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock
        );
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Label::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('label', $this->_label->getType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Label::getElementHtml
     */
    public function testGetElementHtml()
    {
        $this->_label->setValue('Label Text');
        $html = $this->_label->getElementHtml();
        $this->assertContains("<div class=\"control-value\">Label Text", $html);
        $this->_label->setBold(true);
        $html = $this->_label->getElementHtml();
        $this->assertContains("<div class=\"control-value special\">Label Text", $html);
    }
}
