<?php
/**
 * Test JSON Renderer for REST.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Rest\Response\Renderer;

use Magento\Framework\Json\Encoder;
use Magento\Framework\Webapi\Rest\Response\Renderer\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    /** @var Json */
    protected $_restJsonRenderer;

    /** @var Encoder */
    protected $encoderMock;

    protected function setUp(): void
    {
        /** Prepare mocks and objects for SUT constructor. */
        $this->encoderMock = $this->getMockBuilder(Encoder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['encode'])
            ->getMock();
        /** Initialize SUT. */
        $this->_restJsonRenderer = new Json($this->encoderMock);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($this->encoderMock);
        unset($this->_restJsonRenderer);
        parent::tearDown();
    }

    /**
     * Test render method.
     */
    public function testRender()
    {
        $arrayToRender = ['key' => 'value'];
        /** Assert that jsonEncode method in mocked helper will run once */
        $this->encoderMock->expects($this->once())->method('encode');
        $this->_restJsonRenderer->render($arrayToRender);
    }

    /**
     * Test GetMimeType method.
     */
    public function testGetMimeType()
    {
        $expectedMimeType = 'application/json';
        $this->assertEquals($expectedMimeType, $this->_restJsonRenderer->getMimeType(), 'Unexpected mime type.');
    }
}
