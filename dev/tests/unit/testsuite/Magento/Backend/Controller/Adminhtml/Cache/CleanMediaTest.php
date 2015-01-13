<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

class CleanMediaTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        // Wire object with mocks
        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $backendHelper = $this->getMock('Magento\Backend\Helper\Data', [], [], '', false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

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
            ['getRequest', 'getResponse', 'getMessageManager', 'getSession'],
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
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($response));
        $context->expects($this->once())->method('getSession')->will($this->returnValue($session));
        $context->expects($this->once())->method('getMessageManager')->will($this->returnValue($messageManager));

        $resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirectFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($resultRedirect);

        $controller = $helper->getObject(
            'Magento\Backend\Controller\Adminhtml\Cache\CleanMedia',
            [
                'context' => $context,
                'resultRedirectFactory' => $resultRedirectFactory
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
