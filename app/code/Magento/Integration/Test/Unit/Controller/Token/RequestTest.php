<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Controller\Token;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequestTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\Oauth\Helper\Request|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Integration\Controller\Token\Request
     */
    protected $requestAction;

    protected function setUp(): void
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
        /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        /** @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit\Framework\MockObject\MockObject */
        $update = $this->createMock(\Magento\Framework\View\Layout\ProcessorInterface::class);
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
        $view = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $view->expects($this->any())->method('getLayout')->willReturn($layout);

        /** @var Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject */
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
        $this->frameworkOauthSvcMock = $this->createMock(\Magento\Framework\Oauth\OauthInterface::class);

        /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager $objectManagerHelper */
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->requestAction = $this->objectManagerHelper->getObject(
            \Magento\Integration\Controller\Token\Request::class,
            [
                'context' => $this->context,
                'oauthService'=> $this->frameworkOauthSvcMock,
                'helper' => $this->helperMock,
            ]
        );
    }

    /**
     * Test the basic Request action.
     */
    public function testRequestAction()
    {
        $this->request->expects($this->any())
            ->method('getMethod')
            ->willReturn('GET');
        $this->helperMock->expects($this->once())
            ->method('getRequestUrl');
        $this->helperMock->expects($this->once())
            ->method('prepareRequest');
        $this->frameworkOauthSvcMock->expects($this->once())
            ->method('getRequestToken')
            ->willReturn(['response']);
        $this->response->expects($this->once())
            ->method('setBody');
        $this->requestAction->execute();
    }
}
