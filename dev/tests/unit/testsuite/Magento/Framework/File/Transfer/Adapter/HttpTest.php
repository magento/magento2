<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\File\Transfer\Adapter;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Controller\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    /**
     * @var \Magento\Framework\File\Mime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mime;

    protected function setUp()
    {
        $this->response = $this->getMock('\Magento\Framework\Controller\Response\Http');
        $this->mime = $this->getMock('Magento\Framework\File\Mime');
        $this->object = new Http($this->response, $this->mime);
    }

    public function testSend()
    {
        $file = __DIR__ . '/../../_files/javascript.js';
        $contentType = 'content/type';

        $this->response->expects($this->at(0))
            ->method('setHeader')
            ->with('Content-length', filesize($file));
        $this->response->expects($this->at(1))
            ->method('setHeader')
            ->with('Content-Type', $contentType);
        $this->response->expects($this->once())
            ->method('sendHeaders');
        $this->mime->expects($this->once())
            ->method('getMimeType')
            ->with($file)
            ->will($this->returnValue($contentType));
        $this->expectOutputString(file_get_contents($file));

        $this->object->send($file);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filename is not set
     */
    public function testSendNoFileSpecifiedException()
    {
        $this->object->send([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File 'nonexistent.file' does not exists
     */
    public function testSendNoFileExistException()
    {
        $this->object->send('nonexistent.file');
    }
}
