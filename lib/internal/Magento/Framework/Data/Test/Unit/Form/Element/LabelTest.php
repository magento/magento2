<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Label
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

class LabelTest extends \PHPUnit\Framework\TestCase
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
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
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
