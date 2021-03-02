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
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager $objectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Oauth\OauthInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $frameworkOauthSvcMock;

    /**
     * @var \Magento\Integration\Api\OauthServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $intOauthServiceMock;

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $integrationServiceMock;

    /**
     * @var \Magento\Framework\Oauth\Helper\Request|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Integration\Controller\Token\Access
     */
    protected $accessAction;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->addMethods(['getMethod'])
            ->onlyMethods(
                [
                    'getModuleName',
                    'setModuleName',
                    'getActionName',
                    'setActionName',
                    'getParam',
                    'setParams',
                    'getParams',
                    'getCookie',
                    'isSecure'
                ]
            )
            ->getMockForAbstractClass();
        $this->response = $this->createMock(\Magento\Framework\App\Console\Response::class);
        /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
        $objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
        $eventManager = $this->getMockForAbstractClass(\Magento\Framework\Event\ManagerInterface::class);
        /** @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit\Framework\MockObject\MockObject */
        $update = $this->getMockForAbstractClass(\Magento\Framework\View\Layout\ProcessorInterface::class);
        /** @var \Magento\Framework\View\Layout|\PHPUnit\Framework\MockObject\MockObject */
        $layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $layout->expects($this->any())->method('getUpdate')->willReturn($update);
        /** @var \Magento\Framework\View\Page\Config */
        $pageConfig = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $pageConfig->expects($this->any())->method('addBodyClass')->willReturnSelf();
        /** @var \Magento\Framework\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject */
        $page = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getConfig', 'initLayout', 'addPageLayoutHandles', 'getLayout']
        );
        $page->expects($this->any())->method('getConfig')->willReturn($pageConfig);
        $page->expects($this->any())->method('addPageLayoutHandles')->willReturnSelf();
        $page->expects($this->any())->method('getLayout')->willReturn($layout);

        /** @var \Magento\Framework\App\ViewInterface|\PHPUnit\Framework\MockObject\MockObject */
        $view = $this->getMockForAbstractClass(\Magento\Framework\App\ViewInterface::class);
        $view->expects($this->any())->method('getLayout')->willReturn($layout);

        /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject */
        $resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $resultFactory->expects($this->any())->method('create')->willReturn($page);

        $this->context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->any())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->any())->method('getObjectManager')
            ->willReturn($objectManager);
        $this->context->expects($this->any())->method('getEventManager')->willReturn($eventManager);
        $this->context->expects($this->any())->method('getView')->willReturn($view);
        $this->context->expects($this->any())->method('getResultFactory')
            ->willReturn($resultFactory);

        $this->helperMock = $this->createMock(\Magento\Framework\Oauth\Helper\Request::class);
        $this->frameworkOauthSvcMock = $this->getMockForAbstractClass(\Magento\Framework\Oauth\OauthInterface::class);
        $this->intOauthServiceMock = $this->getMockForAbstractClass(
            \Magento\Integration\Api\OauthServiceInterface::class
        );
        $this->integrationServiceMock = $this->getMockForAbstractClass(
            \Magento\Integration\Api\IntegrationServiceInterface::class
        );
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
            ->method('prepareRequest')
            ->willReturn(['oauth_consumer_key' => 'oauth_key']);
        $this->frameworkOauthSvcMock->expects($this->once())
            ->method('getAccessToken')
            ->willReturn(['response']);
        /** @var \Magento\Integration\Model\Oauth\Consumer|\PHPUnit\Framework\MockObject\MockObject */
        $consumerMock = $this->createMock(\Magento\Integration\Model\Oauth\Consumer::class);
        $consumerMock->expects($this->once())
            ->method('getId');
        $this->intOauthServiceMock->expects($this->once())
            ->method('loadConsumerByKey')
            ->willReturn($consumerMock);
        /** @var \Magento\Integration\Model\Integration|\PHPUnit\Framework\MockObject\MockObject */
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
