<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Log\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class VisitorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Log\Model\Visitor
     */
    protected $visitor;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionManagerInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Framework\HTTP\Header|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $header;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $remoteAddress;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\ServerAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serverAddress;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Customer\Model\Resource\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    public function setUp()
    {
        $this->registry = $this->getMock('Magento\Framework\Registry');
        $this->sessionManagerInterface = $this->getMock('Magento\Framework\Session\SessionManagerInterface');
        $this->storeManagerInterface = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->header = $this->getMock('Magento\Framework\HTTP\Header', [], [], '', false);
        $this->remoteAddress = $this->getMock('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress', [], [], '', false);
        $this->serverAddress = $this->getMock('Magento\Framework\HTTP\PhpEnvironment\ServerAddress', [], [], '', false);
        $this->dateTime = $this->getMock('Magento\Framework\Stdlib\DateTime');

        $store = $this->getMock('\Magento\Store\Model\Store', [], [], '', false, false);
        $this->storeManagerInterface->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->resource = $this->getMockBuilder('Magento\Customer\Model\Resource\Visitor')
            ->setMethods([
                'beginTransaction',
                '__sleep',
                '__wakeup',
                'getIdFieldName',
                'save',
                'addCommitCallback',
                'commit',
            ])->disableOriginalConstructor()->getMock();
        $this->resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('visitor_id'));
        $this->resource->expects($this->any())->method('addCommitCallback')->will($this->returnSelf());

        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            'Magento\Log\Model\Visitor',
            [
                'registry' => $this->registry,
                'session' => $this->sessionManagerInterface,
                'storeManager' => $this->storeManagerInterface,
                'httpHeader' => $this->header,
                'remoteAddress' => $this->remoteAddress,
                'serverAddress' => $this->serverAddress,
                'dateTime' => $this->dateTime,
                'resource' => $this->resource
            ]
        );

        $this->visitor = $objectManagerHelper->getObject('Magento\Log\Model\Visitor', $arguments);
    }

    public function testInitServerData()
    {
        $data = [
            'server_addr', 'remote_addr', 'http_secure', 'http_host', 'http_user_agent',
            'http_accept_language', 'http_accept_charset', 'request_uri', 'http_referer',
        ];
        $result = array_diff($data, array_keys($this->visitor->initServerData()->getData()));
        $this->assertEmpty($result);
    }

    public function testGetUrl()
    {
        $this->visitor->setData([
            'http_secure' => false,
            'http_host' => 'magento.com',
            'request_uri' => '/?some=query',
        ]);
        $this->assertEquals('http://magento.com/?some=query', $this->visitor->getUrl());
    }

    public function testGetFirstVisitAt()
    {
        $time = time();
        $this->dateTime->expects($this->once())->method('now')->will($this->returnValue($time));
        $this->assertEquals($time, $this->visitor->getFirstVisitAt());
    }

    public function testGetLastVisitAt()
    {
        $time = time();
        $this->dateTime->expects($this->once())->method('now')->will($this->returnValue($time));
        $this->assertEquals($time, $this->visitor->getLastVisitAt());
    }

    public function testLogNewVisitor()
    {
        $visitor = $this->getMockBuilder('Magento\Customer\Model\Visitor')
            ->disableOriginalConstructor()->getMock();
        $visitor->expects($this->once())->method('setData')->will($this->returnSelf());
        $visitor->expects($this->once())->method('getData')->will($this->returnValue([]));

        $this->resource->expects($this->once())->method('save')->will($this->returnSelf());
        $this->resource->expects($this->never())->method('beginTransaction');

        $event = new \Magento\Framework\Object(['visitor' => $visitor]);
        $observer = new \Magento\Framework\Object(['event' => $event]);
        $this->assertSame($this->visitor, $this->visitor->logNewVisitor($observer));
    }

    public function testLogVisitorActivity()
    {
        $visitor = $this->getMockBuilder('Magento\Customer\Model\Visitor')
            ->disableOriginalConstructor()->getMock();
        $visitor->expects($this->once())->method('setData')->will($this->returnSelf());
        $visitor->expects($this->once())->method('getData')->will($this->returnValue(['visitor_id' => 1]));

        $this->resource->expects($this->once())->method('save')->will($this->returnSelf());
        $this->resource->expects($this->never())->method('beginTransaction');

        $event = new \Magento\Framework\Object(['visitor' => $visitor]);
        $observer = new \Magento\Framework\Object(['event' => $event]);
        $this->assertSame($this->visitor, $this->visitor->logVisitorActivity($observer));
    }
}
