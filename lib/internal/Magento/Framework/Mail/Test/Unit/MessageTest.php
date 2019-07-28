<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Test\Unit;

/**
 * test Magento\Framework\Mail\Message
 */
class MessageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Mail\Message
     */
    protected $message;

    protected function setUp()
    {
        $this->message = new \Magento\Framework\Mail\Message();
    }

    public function testSetBodyHtml()
    {
        $this->message->setBodyHtml('body');

        $part = $this->message->getBody()->getParts()[0];
        $this->assertEquals('text/html', $part->getType());
        $this->assertEquals('quoted-printable', $part->getEncoding());
        $this->assertEquals('utf-8', $part->getCharset());
        $this->assertEquals('body', $part->getContent());
        $this->assertEquals('inline', $part->getDisposition());
    }

    public function testSetBodyText()
    {
        $this->message->setBodyText('body');

        $part = $this->message->getBody()->getParts()[0];
        $this->assertEquals('text/plain', $part->getType());
        $this->assertEquals('quoted-printable', $part->getEncoding());
        $this->assertEquals('utf-8', $part->getCharset());
        $this->assertEquals('body', $part->getContent());
        $this->assertEquals('inline', $part->getDisposition());
    }
}
