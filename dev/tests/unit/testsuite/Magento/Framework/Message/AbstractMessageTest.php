<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\AbstractMessage test case
 */
class AbstractMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\AbstractMessage
     */
    protected $model;

    public function setUp()
    {
        $this->model = $this->getMockBuilder(
            'Magento\Framework\Message\AbstractMessage'
        )->disableOriginalConstructor()->setMethods(
            ['getType']
        )->getMockForAbstractClass();
    }

    /**
     * @covers \Magento\Framework\Message\AbstractMessage::getText
     * @covers \Magento\Framework\Message\AbstractMessage::setText
     * @dataProvider setTextGetTextProvider
     */
    public function testSetTextGetText($text)
    {
        $this->model->setText($text);
        $this->assertEquals($text, $this->model->getText());
    }

    /**
     * @return array
     */
    public function setTextGetTextProvider()
    {
        return [[''], ['some text']];
    }

    /**
     * @covers \Magento\Framework\Message\AbstractMessage::getIdentifier
     * @covers \Magento\Framework\Message\AbstractMessage::setIdentifier
     * @dataProvider setIdentifierGetIdentifierProvider
     */
    public function testSetIdentifierGetIdentifier($identifier)
    {
        $this->model->setIdentifier($identifier);
        $this->assertEquals($identifier, $this->model->getIdentifier());
    }

    /**
     * @return array
     */
    public function setIdentifierGetIdentifierProvider()
    {
        return [[''], ['some identifier']];
    }

    /**
     * @covers \Magento\Framework\Message\AbstractMessage::getIsSticky
     * @covers \Magento\Framework\Message\AbstractMessage::setIsSticky
     */
    public function testSetIsStickyGetIsSticky()
    {
        $this->assertFalse($this->model->getIsSticky());
        $this->model->setIsSticky();
        $this->assertTrue($this->model->getIsSticky());
    }

    /**
     * @covers \Magento\Framework\Message\AbstractMessage::toString
     */
    public function testToString()
    {
        $someText = 'some text';
        $expectedString = MessageInterface::TYPE_SUCCESS . ': ' . $someText;

        $this->model->expects(
            $this->atLeastOnce()
        )->method(
            'getType'
        )->will(
            $this->returnValue(MessageInterface::TYPE_SUCCESS)
        );

        $this->model->setText($someText);
        $this->assertEquals($expectedString, $this->model->toString());
    }
}
