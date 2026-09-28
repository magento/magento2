<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Cache;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Cache\CleanMedia;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ExceptionMessageLookupFactory;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Asset\MergeService;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CleanMediaTest extends TestCase
{
    use MockCreationTrait;

    public function testExecute()
    {
        // Wire object with mocks
        $response = $this->createMock(Http::class);
        $request = $this->createMock(RequestHttp::class);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $backendHelper = $this->createMock(Data::class);
        $helper = new ObjectManager($this);

        $session = $this->createPartialMockWithReflection(
            Session::class,
            ['setIsUrlNotice']
        );

        $exceptionMessageFactory = $this->createPartialMockWithReflection(
            ExceptionMessageLookupFactory::class,
            ['getMessageGenerator']
        );

        $messageManagerParams = $helper->getConstructArguments(Manager::class);
        $messageManagerParams['exceptionMessageFactory'] = $exceptionMessageFactory;
        $messageManager = $this->getMockBuilder(Manager::class)
            ->onlyMethods(['addSuccessMessage'])
            ->setConstructorArgs($messageManagerParams)
            ->getMock();

        $args = $helper->getConstructArguments(
            Context::class,
            [
                'session' => $session,
                'response' => $response,
                'objectManager' => $objectManager,
                'helper' => $backendHelper,
                'request' => $request,
                'messageManager' => $messageManager
            ]
        );
        $context = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getRequest', 'getResponse', 'getMessageManager', 'getSession', 'getResultFactory'])
            ->setConstructorArgs($args)
            ->getMock();
        $resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $resultRedirect = $this->createMock(Redirect::class);
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);
        $context->expects($this->once())->method('getRequest')->willReturn($request);
        $context->expects($this->once())->method('getResponse')->willReturn($response);
        $context->expects($this->once())->method('getSession')->willReturn($session);
        $context->expects($this->once())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->once())->method('getResultFactory')->willReturn($resultFactory);

        $controller = $helper->getObject(
            CleanMedia::class,
            [
                'context' => $context
            ]
        );

        // Setup expectations
        $mergeService = $this->createMock(MergeService::class);
        $mergeService->expects($this->once())->method('cleanMergedJsCss');

        $messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The JavaScript/CSS cache has been cleaned.');

        $valueMap = [
            [MergeService::class, $mergeService],
            [SessionManager::class, $session],
        ];
        $objectManager->expects($this->any())->method('get')->willReturnMap($valueMap);

        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*')
            ->willReturnSelf();

        // Run
        $controller->execute();
    }
}
