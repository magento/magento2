<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class VisitorTest
 * @package Magento\Customer\Model
 */
class VisitorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $visitor;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Resource\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    protected function setUp()
    {
        $this->registry = $this->getMock('Magento\Framework\Registry');
        $this->session = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['getSessionId', 'getVisitorData', 'setVisitorData'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->resource = $this->getMockBuilder('Magento\Customer\Model\Resource\Visitor')
            ->setMethods([
                'beginTransaction',
                '__sleep',
                '__wakeup',
                'getIdFieldName',
                'save',
                'addCommitCallback',
                'commit',
                'clean',
            ])->disableOriginalConstructor()->getMock();
        $this->resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('visitor_id'));
        $this->resource->expects($this->any())->method('addCommitCallback')->will($this->returnSelf());

        $arguments = $this->objectManagerHelper->getConstructArguments(
            'Magento\Customer\Model\Visitor',
            [
                'registry' => $this->registry,
                'session' => $this->session,
                'resource' => $this->resource
            ]
        );

        $this->visitor = $this->objectManagerHelper->getObject('Magento\Customer\Model\Visitor', $arguments);
    }

    public function testInitByRequest()
    {
        $this->session->expects($this->once())->method('getSessionId')
            ->will($this->returnValue('asdfhasdfjhkj2198sadf8sdf897'));
        $this->visitor->initByRequest(null);
        $this->assertEquals('asdfhasdfjhkj2198sadf8sdf897', $this->visitor->getSessionId());

        $this->visitor->setData(['visitor_id' => 1]);
        $this->visitor->initByRequest(null);
        $this->assertNull($this->visitor->getSessionId());
    }

    public function testSaveByRequest()
    {
        $this->session->expects($this->once())->method('setVisitorData')->will($this->returnSelf());
        $this->assertSame($this->visitor, $this->visitor->saveByRequest(null));
    }

    public function testIsModuleIgnored()
    {
        $this->visitor = $this->objectManagerHelper->getObject(
            'Magento\Customer\Model\Visitor',
            [
                'registry' => $this->registry,
                'session' => $this->session,
                'resource' => $this->resource,
                'ignores' => ['test_route_name' => true]
            ]
        );
        $request = new \Magento\Framework\Object(['route_name' => 'test_route_name']);
        $action =  new \Magento\Framework\Object(['request' => $request]);
        $event =  new \Magento\Framework\Object(['controller_action' => $action]);
        $observer = new \Magento\Framework\Object(['event' => $event]);
        $this->assertTrue($this->visitor->isModuleIgnored($observer));
    }

    public function testBindCustomerLogin()
    {
        $customer = new \Magento\Framework\Object(['id' => '1']);
        $observer = new \Magento\Framework\Object([
            'event' => new \Magento\Framework\Object(['customer' => $customer]),
        ]);

        $this->visitor->bindCustomerLogin($observer);
        $this->assertTrue($this->visitor->getDoCustomerLogin());
        $this->assertEquals($customer->getId(), $this->visitor->getCustomerId());

        $this->visitor->unsetData();
        $this->visitor->setCustomerId('2');
        $this->visitor->bindCustomerLogin($observer);
        $this->assertNull($this->visitor->getDoCustomerLogin());
        $this->assertEquals('2', $this->visitor->getCustomerId());
    }

    public function testBindCustomerLogout()
    {
        $observer = new \Magento\Framework\Object();

        $this->visitor->setCustomerId('1');
        $this->visitor->bindCustomerLogout($observer);
        $this->assertTrue($this->visitor->getDoCustomerLogout());

        $this->visitor->unsetData();
        $this->visitor->bindCustomerLogout($observer);
        $this->assertNull($this->visitor->getDoCustomerLogout());
    }

    public function testBindQuoteCreate()
    {
        $quote = new \Magento\Framework\Object(['id' => '1', 'is_checkout_cart' => true]);
        $observer = new \Magento\Framework\Object([
            'event' => new \Magento\Framework\Object(['quote' => $quote]),
        ]);
        $this->visitor->bindQuoteCreate($observer);
        $this->assertTrue($this->visitor->getDoQuoteCreate());
    }

    public function testBindQuoteDestroy()
    {
        $quote = new \Magento\Framework\Object(['id' => '1']);
        $observer = new \Magento\Framework\Object([
            'event' => new \Magento\Framework\Object(['quote' => $quote]),
        ]);
        $this->visitor->bindQuoteDestroy($observer);
        $this->assertTrue($this->visitor->getDoQuoteDestroy());
    }

    public function testClean()
    {
        $this->resource->expects($this->once())->method('clean')->with($this->visitor)->will($this->returnSelf());
        $this->visitor->clean();
    }
}
