<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Cache;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CleanMediaTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        // Wire object with mocks
        $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $backendHelper = $this->createMock(\Magento\Backend\Helper\Data::class);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $session = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->setMethods(['setIsUrlNotice'])
            ->setConstructorArgs($helper->getConstructArguments(\Magento\Backend\Model\Session::class))
            ->getMock();

        $exceptionMessageFactory = $this->getMockBuilder(
            \Magento\Framework\Message\ExceptionMessageLookupFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(
                ['getMessageGenerator']
            )
            ->getMock();

        $messageManagerParams = $helper->getConstructArguments(\Magento\Framework\Message\Manager::class);
        $messageManagerParams['exceptionMessageFactory'] = $exceptionMessageFactory;
        $messageManager = $this->getMockBuilder(\Magento\Framework\Message\Manager::class)
            ->setMethods(['addSuccessMessage'])
            ->setConstructorArgs($messageManagerParams)
            ->getMock();

        $args = $helper->getConstructArguments(
            \Magento\Backend\App\Action\Context::class,
            [
                'session' => $session,
                'response' => $response,
                'objectManager' => $objectManager,
                'helper' => $backendHelper,
                'request' => $request,
                'messageManager' => $messageManager
            ]
        );
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->setMethods(['getRequest', 'getResponse', 'getMessageManager', 'getSession', 'getResultFactory'])
            ->setConstructorArgs($args)
            ->getMock();
        $resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);
        $context->expects($this->once())->method('getRequest')->willReturn($request);
        $context->expects($this->once())->method('getResponse')->willReturn($response);
        $context->expects($this->once())->method('getSession')->willReturn($session);
        $context->expects($this->once())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->once())->method('getResultFactory')->willReturn($resultFactory);

        $controller = $helper->getObject(
            \Magento\Backend\Controller\Adminhtml\Cache\CleanMedia::class,
            [
                'context' => $context
            ]
        );

        // Setup expectations
        $mergeService = $this->createMock(\Magento\Framework\View\Asset\MergeService::class);
        $mergeService->expects($this->once())->method('cleanMergedJsCss');

        $messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The JavaScript/CSS cache has been cleaned.');

        $valueMap = [
            [\Magento\Framework\View\Asset\MergeService::class, $mergeService],
            [\Magento\Framework\Session\SessionManager::class, $session],
        ];
        $objectManager->expects($this->any())->method('get')->will($this->returnValueMap($valueMap));

        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*')
            ->willReturnSelf();

        // Run
        $controller->execute();
    }
}
