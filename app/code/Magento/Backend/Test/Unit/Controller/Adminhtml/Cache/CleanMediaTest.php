<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Cache;

class CleanMediaTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        // Wire object with mocks
        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $backendHelper = $this->getMock('Magento\Backend\Helper\Data', [], [], '', false);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $session = $this->getMock(
            'Magento\Backend\Model\Session',
            ['setIsUrlNotice'],
            $helper->getConstructArguments('Magento\Backend\Model\Session')
        );
        $messageManager = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addSuccess'],
            $helper->getConstructArguments('Magento\Framework\Message\Manager')
        );
        $context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            ['getRequest', 'getResponse', 'getMessageManager', 'getSession', 'getResultFactory'],
            $helper->getConstructArguments(
                'Magento\Backend\App\Action\Context',
                [
                    'session' => $session,
                    'response' => $response,
                    'objectManager' => $objectManager,
                    'helper' => $backendHelper,
                    'request' => $request,
                    'messageManager' => $messageManager
                ]
            )
        );
        $resultFactory = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
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
            'Magento\Backend\Controller\Adminhtml\Cache\CleanMedia',
            [
                'context' => $context
            ]
        );

        // Setup expectations
        $mergeService = $this->getMock('Magento\Framework\View\Asset\MergeService', [], [], '', false);
        $mergeService->expects($this->once())->method('cleanMergedJsCss');

        $messageManager->expects($this->once())
            ->method('addSuccess')
            ->with('The JavaScript/CSS cache has been cleaned.'
        );

        $valueMap = [
            ['Magento\Framework\View\Asset\MergeService', $mergeService],
            ['Magento\Framework\Session\SessionManager', $session],
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
