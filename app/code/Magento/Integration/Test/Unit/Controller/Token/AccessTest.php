<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Controller\Token;

class AccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $update;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /**
     * @var Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $page;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager $objectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Oauth\OauthInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $frameworkOauthSvcMock;

    /**
     * @var \Magento\Integration\Api\OauthServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $intOauthServiceMock;

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $integrationServiceMock;

    /**
     * @var \Magento\Framework\Oauth\Helper\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Integration\Controller\Token\Access
     */
    protected $accessAction;

    protected function setUp()
    {
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->response = $this->getMock('Magento\Framework\App\Console\Response');
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface');

        $this->update = $this->getMock('Magento\Framework\View\Layout\ProcessorInterface');
        $this->layout = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->layout->expects($this->any())->method('getUpdate')->will($this->returnValue($this->update));

        $this->pageConfig = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()->getMock();
        $this->pageConfig->expects($this->any())->method('addBodyClass')->will($this->returnSelf());

        $this->page = $this->getMockBuilder('Magento\Framework\View\Page')
            ->setMethods(['getConfig', 'initLayout', 'addPageLayoutHandles', 'getLayout'])
            ->disableOriginalConstructor()->getMock();
        $this->page->expects($this->any())->method('getConfig')->will($this->returnValue($this->pageConfig));
        $this->page->expects($this->any())->method('addPageLayoutHandles')->will($this->returnSelf());
        $this->page->expects($this->any())->method('getLayout')->will($this->returnValue($this->layout));

        $this->view = $this->getMock('Magento\Framework\App\ViewInterface');
        $this->view->expects($this->any())->method('getLayout')->will($this->returnValue($this->layout));

        $this->resultFactory = $this->getMock('Magento\Framework\Controller\ResultFactory', [], [], '', false);
        $this->resultFactory->expects($this->any())->method('create')->will($this->returnValue($this->page));

        $this->context = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->context->expects($this->any())->method('getObjectManager')
            ->will($this->returnValue($this->objectManager));
        $this->context->expects($this->any())->method('getEventManager')->will($this->returnValue($this->eventManager));
        $this->context->expects($this->any())->method('getView')->will($this->returnValue($this->view));
        $this->context->expects($this->any())->method('getResultFactory')
            ->will($this->returnValue($this->resultFactory));

        $this->helperMock = $this->getMockBuilder('Magento\Framework\Oauth\Helper\Request')
            ->disableOriginalConstructor()->getMock();
        $this->frameworkOauthSvcMock = $this->getMockBuilder('Magento\Framework\Oauth\OauthInterface')
            ->disableOriginalConstructor()->getMock();
        $this->intOauthServiceMock = $this->getMockBuilder('Magento\Integration\Api\OauthServiceInterface')
            ->disableOriginalConstructor()->getMock();
        $this->integrationServiceMock = $this->getMockBuilder('Magento\Integration\Api\IntegrationServiceInterface')
            ->disableOriginalConstructor()->getMock();

        /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager $objectManagerHelper */
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->accessAction = $this->objectManagerHelper->getObject(
            'Magento\Integration\Controller\Token\Access', [
                'context' => $this->context,
                'oauthService'=> $this->frameworkOauthSvcMock,
                'intOauthService' => $this->intOauthServiceMock,
                'integrationService' => $this->integrationServiceMock,
                'helper' => $this->helperMock,
            ]
        );
    }

    /**
     * Test the basic Access action.
     */
    public function testAccessAction()
    {
        $this->helperMock->expects($this->once())
            ->method('getRequestUrl');
        $this->helperMock->expects($this->once())
            ->method('prepareRequest');
        $this->frameworkOauthSvcMock->expects($this->once())
            ->method('getAccessToken')
            ->willReturn(['response']);
        /** @var \Magento\Integration\Model\Oauth\Consumer|\PHPUnit_Framework_MockObject_MockObject */
        $consumerMock = $this->getMockBuilder('Magento\Integration\Model\Oauth\Consumer')
            ->disableOriginalConstructor()->getMock();
        $consumerMock->expects($this->once())
            ->method('getId');
        $this->intOauthServiceMock->expects($this->once())
            ->method('loadConsumerByKey')
            ->willReturn($consumerMock);
        /** @var \Magento\Integration\Model\Integration|\PHPUnit_Framework_MockObject_MockObject */
        $integrationMock = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()->getMock();
        $integrationMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->integrationServiceMock->expects($this->once())
            ->method('findByConsumerId')
            ->willReturn($integrationMock);
        $this->response->expects($this->once())
            ->method('setBody');

        $this->accessAction->execute();
    }
}
