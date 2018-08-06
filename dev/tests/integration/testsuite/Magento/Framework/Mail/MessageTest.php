<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

class MessageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Message
     */
    private $message;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->message = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(Message::class);
    }

    public function testGetHeaderEncodingDefaultValue()
    {
        $this->assertEquals(\Zend_Mime::ENCODING_BASE64, $this->message->getHeaderEncoding());
    }

    public function testGetCharsetDefaultValue()
    {
        $this->assertEquals('utf-8', $this->message->getCharset());
    }
}
