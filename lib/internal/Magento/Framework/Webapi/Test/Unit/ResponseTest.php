<?php
/**
 * Test for Webapi Response model.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit;

use Magento\Framework\Webapi\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /**
     * Response object.
     *
     * @var \Magento\Framework\Webapi\Response
     */
    protected $_response;

    protected function setUp(): void
    {
        /** Initialize SUT. */
        $this->_response = new Response();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($this->_response);
        parent::tearDown();
    }

    /**
     * Test addMessage, hasMessage, getMessage, and clearMessages methods.
     */
    public function testMessagesCrud()
    {
        /** Test that new object does not contain any messages. */
        $this->assertFalse($this->_response->hasMessages(), 'New object contains messages.');

        /** Test message adding functionality. */
        $this->_response->addMessage(
            'Message text',
            Response::HTTP_OK,
            ['key' => 'value'],
            Response::MESSAGE_TYPE_SUCCESS
        );
        $this->assertTrue($this->_response->hasMessages(), 'New message is not added correctly.');

        /** Test message getting functionality. */
        $expectedMessage = [
            Response::MESSAGE_TYPE_SUCCESS => [
                [
                    'key' => 'value',
                    'message' => 'Message text',
                    'code' => Response::HTTP_OK,
                ],
            ],
        ];
        $this->assertEquals($expectedMessage, $this->_response->getMessages(), 'Message is got incorrectly.');

        /** Test message clearing functionality. */
        $this->_response->clearMessages();
        $this->assertFalse($this->_response->hasMessages(), 'Message is not cleared.');
    }
}
