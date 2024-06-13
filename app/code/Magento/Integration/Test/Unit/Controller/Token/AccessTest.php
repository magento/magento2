<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Controller\Token;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Console\Response;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Oauth\Helper\Request;
use Magento\Framework\Oauth\OauthInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Result\Page;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Controller\Token\Access;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Oauth\Consumer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccessTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $response;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var ObjectManager $objectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var OauthInterface|MockObject
     */
    protected $frameworkOauthSvcMock;

    /**
     * @var OauthServiceInterface|MockObject
     */
    protected $intOauthServiceMock;

    /**
     * @var IntegrationServiceInterface|MockObject
     */
    protected $integrationServiceMock;

    /**
     * @var Request|MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Integration\Controller\Token\Access
     */
    protected $accessAction;

    protected function setUp(): void
    {
        $this->request = $this->createPartialMockWithReflection(
            RequestInterface::class,
            [
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
            ]
        );
        $this->response = $this->createMock(Response::class);
        /** @var ObjectManagerInterface|MockObject */
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        /** @var ManagerInterface|MockObject */
        $eventManager = $this->createMock(ManagerInterface::class);
        /** @var ProcessorInterface|MockObject */
        $update = $this->createMock(ProcessorInterface::class);
        /** @var Layout|MockObject */
        $layout = $this->createMock(Layout::class);
        $layout->expects($this->any())->method('getUpdate')->willReturn($update);
        /** @var Config */
        $pageConfig = $this->createMock(Config::class);
        $pageConfig->expects($this->any())->method('addBodyClass')->willReturnSelf();
        /** @var Page|MockObject */
        $page = $this->createPartialMock(
            Page::class,
            ['getConfig', 'initLayout', 'addPageLayoutHandles', 'getLayout']
        );
        $page->expects($this->any())->method('getConfig')->willReturn($pageConfig);
        $page->expects($this->any())->method('addPageLayoutHandles')->willReturnSelf();
        $page->expects($this->any())->method('getLayout')->willReturn($layout);

        /** @var ViewInterface|MockObject */
        $view = $this->createMock(ViewInterface::class);
        $view->expects($this->any())->method('getLayout')->willReturn($layout);

        /** @var ResultFactory|MockObject */
        $resultFactory = $this->createMock(ResultFactory::class);
        $resultFactory->expects($this->any())->method('create')->willReturn($page);

        $this->context = $this->createMock(Context::class);
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->any())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->any())->method('getObjectManager')
            ->willReturn($objectManager);
        $this->context->expects($this->any())->method('getEventManager')->willReturn($eventManager);
        $this->context->expects($this->any())->method('getView')->willReturn($view);
        $this->context->expects($this->any())->method('getResultFactory')
            ->willReturn($resultFactory);

        $this->helperMock = $this->createMock(Request::class);
        $this->frameworkOauthSvcMock = $this->createMock(OauthInterface::class);
        $this->intOauthServiceMock = $this->createMock(OauthServiceInterface::class);
        $this->integrationServiceMock = $this->createMock(IntegrationServiceInterface::class);
        /** @var ObjectManager $objectManagerHelper */
        $this->objectManagerHelper = new ObjectManager($this);
        $this->accessAction = $this->objectManagerHelper->getObject(
            Access::class,
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
    public function testAccessAction(): void
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
        /** @var Consumer|MockObject */
        $consumerMock = $this->createMock(Consumer::class);
        $consumerMock->expects($this->once())
            ->method('getId');
        $this->intOauthServiceMock->expects($this->once())
            ->method('loadConsumerByKey')
            ->willReturn($consumerMock);
        /** @var Integration|MockObject */
        $integrationMock = $this->createMock(Integration::class);
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
