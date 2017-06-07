<?php
/**
 * Test JSON Renderer for REST.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\Rest\Response\Renderer;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Webapi\Rest\Response\Renderer\Json */
    protected $_restJsonRenderer;

    /** @var \Magento\Framework\Json\Encoder */
    protected $encoderMock;

    protected function setUp()
    {
        /** Prepare mocks and objects for SUT constructor. */
        $this->encoderMock = $this->getMockBuilder(\Magento\Framework\Json\Encoder::class)
            ->disableOriginalConstructor()
            ->setMethods(['encode'])
            ->getMock();
        /** Initialize SUT. */
        $this->_restJsonRenderer = new \Magento\Framework\Webapi\Rest\Response\Renderer\Json($this->encoderMock);
        parent::setUp();
    }

    protected function tearDown()
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
