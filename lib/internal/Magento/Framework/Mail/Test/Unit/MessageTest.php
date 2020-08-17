<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail\Test\Unit;

use Magento\Framework\Mail\Message;
use PHPUnit\Framework\TestCase;

/**
 * test Magento\Framework\Mail\Message
 */
class MessageTest extends TestCase
{
    /**
     * @var Message
     */
    protected $message;

    protected function setUp(): void
    {
        $this->message = new Message();
    }

    public function testSetBodyHtml()
    {
        $this->message->setBodyHtml('body');

        $part = $this->message->getBody()->getParts()[0];
        $this->assertEquals('text/html', $part->getType());
        $this->assertEquals('quoted-printable', $part->getEncoding());
        $this->assertEquals('utf-8', $part->getCharset());
        $this->assertEquals('body', $part->getContent());
    }

    public function testSetBodyText()
    {
        $this->message->setBodyText('body');

        $part = $this->message->getBody()->getParts()[0];
        $this->assertEquals('text/plain', $part->getType());
        $this->assertEquals('quoted-printable', $part->getEncoding());
        $this->assertEquals('utf-8', $part->getCharset());
        $this->assertEquals('body', $part->getContent());
    }
}
