<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\MessageInterface;

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
        $this->model = new TestingMessage();
    }

    /**
     * @covers \Magento\Framework\Message\AbstractMessage::getText
     * @covers \Magento\Framework\Message\AbstractMessage::setText
     * @dataProvider setTextGetTextProvider
     */
    public function testSetTextGetText($text, $resultText)
    {
        $this->model->setText($text);
        $this->assertEquals($resultText, $this->model->getText());
    }

    /**
     * @return array
     */
    public function setTextGetTextProvider()
    {
        return [['', ''], ['some text', 'some text'], [new \Magento\Framework\Phrase('some text'), 'some text']];
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
        $expectedString = TestingMessage::TYPE_TESTING . ': testing_message: ' . $someText;

        $this->model->setIdentifier('testing_message');
        $this->model->setText($someText);
        $this->assertEquals($expectedString, $this->model->toString());
    }
}
