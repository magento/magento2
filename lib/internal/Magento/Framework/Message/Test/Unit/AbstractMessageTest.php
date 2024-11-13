<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\AbstractMessage;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

/**
 * \Magento\Framework\Message\AbstractMessage test case
 */
class AbstractMessageTest extends TestCase
{
    /**
     * @var AbstractMessage
     */
    protected $model;

    protected function setUp(): void
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
    public static function setTextGetTextProvider()
    {
        return [['', ''], ['some text', 'some text'], [new Phrase('some text'), 'some text']];
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
    public static function setIdentifierGetIdentifierProvider()
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
