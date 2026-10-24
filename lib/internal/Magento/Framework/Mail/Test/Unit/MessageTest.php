<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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

        $part = $this->message->getBody();
        $this->assertEquals('html', $part->getMediaSubtype());
        $this->assertEquals(
            'quoted-printable',
            $part->getPreparedHeaders()->get('Content-Transfer-Encoding')->getBody()
        );
        $this->assertEquals(
            'utf-8',
            $part->getPreparedHeaders()->get('Content-Transfer-Encoding')->getCharset()
        );
        $this->assertEquals('body', $part->getBody());
        $this->assertEquals('inline', $part->getDisposition());
    }

    public function testSetBodyText()
    {
        $this->message->setBodyText('body');

        $part = $this->message->getBody();
        $this->assertEquals('plain', $part->getMediaSubtype());
        $this->assertEquals(
            'quoted-printable',
            $part->getPreparedHeaders()->get('Content-Transfer-Encoding')->getBody()
        );
        $this->assertEquals('utf-8', $part->getPreparedHeaders()->get('Content-Transfer-Encoding')->getCharset());
        $this->assertEquals('body', $part->getBody());
        $this->assertEquals('inline', $part->getDisposition());
    }
}
