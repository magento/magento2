<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

class PurgeCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\CacheInvalidate\Model\PurgeCache */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\HTTP\Adapter\Curl */
    protected $curlMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\PageCache\Helper\Data */
    protected $helperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->helperMock = $this->getMock('Magento\PageCache\Helper\Data', ['getUrl'], [], '', false);
        $this->curlMock = $this->getMock(
            '\Magento\Framework\HTTP\Adapter\Curl',
            ['setOptions', 'write', 'read', 'close'],
            [],
            '',
            false
        );
        $this->logger = $this->getMock('Magento\Framework\Cache\InvalidateLogger', [], [], '', false);
        $this->model = new \Magento\CacheInvalidate\Model\PurgeCache(
            $this->helperMock,
            $this->curlMock,
            $this->logger
        );
    }

    public function testSendPurgeRequest()
    {
        $tags = 'tags';
        $url = 'http://mangento.index.php';
        $httpVersion = '1.1';
        $headers = ["X-Magento-Tags-Pattern: {$tags}"];
        $this->helperMock->expects(
            $this->any()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('*'),
            []
        )->will(
            $this->returnValue($url)
        );
        $this->curlMock->expects($this->once())->method('setOptions')->with([CURLOPT_CUSTOMREQUEST => 'PURGE']);
        $this->curlMock->expects(
            $this->once()
        )->method(
            'write'
        )->with(
            $this->equalTo(''),
            $this->equalTo($url),
            $httpVersion,
            $this->equalTo($headers)
        );
        $this->curlMock->expects($this->once())->method('read');
        $this->curlMock->expects($this->once())->method('close');
        $this->model->sendPurgeRequest($tags);
    }
}
