<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Controller\Token;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccessTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

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
        $this->request = $this->createPartialMock(\Magento\Framework\App\RequestInterface::class, [
                'getMethod',
                'getModuleName',
                'setModuleName',
                'getActionName',
                'setActionName',
                'getParam',
                'setParams',
                'getParams',
                'getCookie',
                'isSecure'
            ]);
        $this->response = $this->createMock(\Magento\Framework\App\Console\Response::class);
        /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        /** @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $update = $this->createMock(\Magento\Framework\View\Layout\ProcessorInterface::class);
        /** @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject */
        $layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $layout->expects($this->any())->method('getUpdate')->will($this->returnValue($update));
        /** @var \Magento\Framework\View\Page\Config */
        $pageConfig = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $pageConfig->expects($this->any())->method('addBodyClass')->will($this->returnSelf());
        /** @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject */
        $page = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getConfig', 'initLayout', 'addPageLayoutHandles', 'getLayout']
        );
        $page->expects($this->any())->method('getConfig')->will($this->returnValue($pageConfig));
        $page->expects($this->any())->method('addPageLayoutHandles')->will($this->returnSelf());
        $page->expects($this->any())->method('getLayout')->will($this->returnValue($layout));

        /** @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject */
        $view = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $view->expects($this->any())->method('getLayout')->will($this->returnValue($layout));

        /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject */
        $resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $resultFactory->expects($this->any())->method('create')->will($this->returnValue($page));

        $this->context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->context->expects($this->any())->method('getObjectManager')
            ->will($this->returnValue($objectManager));
        $this->context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManager));
        $this->context->expects($this->any())->method('getView')->will($this->returnValue($view));
        $this->context->expects($this->any())->method('getResultFactory')
            ->will($this->returnValue($resultFactory));

        $this->helperMock = $this->createMock(\Magento\Framework\Oauth\Helper\Request::class);
        $this->frameworkOauthSvcMock = $this->createMock(\Magento\Framework\Oauth\OauthInterface::class);
        $this->intOauthServiceMock = $this->createMock(\Magento\Integration\Api\OauthServiceInterface::class);
        $this->integrationServiceMock = $this->createMock(\Magento\Integration\Api\IntegrationServiceInterface::class);
        /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager $objectManagerHelper */
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->accessAction = $this->objectManagerHelper->getObject(
            \Magento\Integration\Controller\Token\Access::class,
            [
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
        $this->request->expects($this->any())
            ->method('getMethod')
            ->willReturn('GET');
        $this->helperMock->expects($this->once())
            ->method('getRequestUrl');
        $this->helperMock->expects($this->once())
            ->method('prepareRequest');
        $this->frameworkOauthSvcMock->expects($this->once())
            ->method('getAccessToken')
            ->willReturn(['response']);
        /** @var \Magento\Integration\Model\Oauth\Consumer|\PHPUnit_Framework_MockObject_MockObject */
        $consumerMock = $this->createMock(\Magento\Integration\Model\Oauth\Consumer::class);
        $consumerMock->expects($this->once())
            ->method('getId');
        $this->intOauthServiceMock->expects($this->once())
            ->method('loadConsumerByKey')
            ->willReturn($consumerMock);
        /** @var \Magento\Integration\Model\Integration|\PHPUnit_Framework_MockObject_MockObject */
        $integrationMock = $this->createMock(\Magento\Integration\Model\Integration::class);
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
