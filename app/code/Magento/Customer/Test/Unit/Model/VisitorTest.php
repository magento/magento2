<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\ResourceModel\Visitor as VisitorResourceModel;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor as VisitorModel;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests to cover Visitor Model
 */
class VisitorTest extends TestCase
{
    /**
     * @var VisitorModel
     */
    protected $visitor;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var VisitorResourceModel|MockObject
     */
    protected $visitorResourceModelMock;

    /**
     * @var SessionManagerInterface|MockObject
     */
    protected $sessionMock;

    /**
     * @var HttpRequest|MockObject
     */
    private $httpRequestMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(Registry::class);
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSessionId', 'getVisitorData', 'setVisitorData'])
            ->getMock();
        $this->httpRequestMock = $this->createMock(HttpRequest::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->visitorResourceModelMock = $this->getMockBuilder(VisitorResourceModel::class)
            ->setMethods([
                'beginTransaction',
                '__sleep',
                '__wakeup',
                'getIdFieldName',
                'save',
                'addCommitCallback',
                'commit',
                'clean',
            ])->disableOriginalConstructor()
            ->getMock();
        $this->visitorResourceModelMock->expects($this->any())->method('getIdFieldName')->willReturn('visitor_id');
        $this->visitorResourceModelMock->expects($this->any())->method('addCommitCallback')->willReturnSelf();

        $arguments = $this->objectManagerHelper->getConstructArguments(
            VisitorModel::class,
            [
                'registry' => $this->registryMock,
                'session' => $this->sessionMock,
                'resource' => $this->visitorResourceModelMock,
                'request' => $this->httpRequestMock,
            ]
        );

        $this->visitor = $this->objectManagerHelper->getObject(VisitorModel::class, $arguments);
    }

    public function testInitByRequest()
    {
        $oldSessionId = 'asdfhasdfjhkj2198sadf8sdf897';
        $newSessionId = 'bsdfhasdfjhkj2198sadf8sdf897';
        $this->sessionMock->expects($this->any())->method('getSessionId')->willReturn($newSessionId);
        $this->sessionMock->expects($this->atLeastOnce())->method('getVisitorData')
            ->willReturn(['session_id' => $oldSessionId]);
        $this->visitor->initByRequest(null);
        $this->assertEquals($newSessionId, $this->visitor->getSessionId());
    }

    public function testSaveByRequest()
    {
        $this->sessionMock->expects($this->once())->method('setVisitorData')->willReturnSelf();
        $this->assertSame($this->visitor, $this->visitor->saveByRequest(null));
    }

    public function testIsModuleIgnored()
    {
        $this->visitor = $this->objectManagerHelper->getObject(
            VisitorModel::class,
            [
                'registry' => $this->registryMock,
                'session' => $this->sessionMock,
                'resource' => $this->visitorResourceModelMock,
                'ignores' => ['test_route_name' => true],
                'requestSafety' => $this->httpRequestMock,
            ]
        );
        $this->httpRequestMock->method('getRouteName')->willReturn('test_route_name');
        $observer = new DataObject();
        $this->assertTrue($this->visitor->isModuleIgnored($observer));
    }

    public function testBindCustomerLogin()
    {
        $customer = new DataObject(['id' => '1']);
        $observer = new DataObject([
            'event' => new DataObject(['customer' => $customer]),
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
        $observer = new DataObject();

        $this->visitor->setCustomerId('1');
        $this->visitor->bindCustomerLogout($observer);
        $this->assertTrue($this->visitor->getDoCustomerLogout());

        $this->visitor->unsetData();
        $this->visitor->bindCustomerLogout($observer);
        $this->assertNull($this->visitor->getDoCustomerLogout());
    }

    public function testBindQuoteCreate()
    {
        $quote = new DataObject(['id' => '1', 'is_checkout_cart' => true]);
        $observer = new DataObject([
            'event' => new DataObject(['quote' => $quote]),
        ]);
        $this->visitor->bindQuoteCreate($observer);
        $this->assertTrue($this->visitor->getDoQuoteCreate());
    }

    public function testBindQuoteDestroy()
    {
        $quote = new DataObject(['id' => '1']);
        $observer = new DataObject([
            'event' => new DataObject(['quote' => $quote]),
        ]);
        $this->visitor->bindQuoteDestroy($observer);
        $this->assertTrue($this->visitor->getDoQuoteDestroy());
    }

    public function testClean()
    {
        $this->visitorResourceModelMock->expects($this->once())
            ->method('clean')
            ->with($this->visitor)
            ->willReturnSelf();
        $this->visitor->clean();
    }
}
