<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Unit\Controller\Adminhtml;


class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Sitemap\Controller\Adminhtml\Sitemap\Save|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $controller;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    protected function setUp()
    {
        $this->request = $this->getMock('Magento\Framework\HTTP\PhpEnvironment\Request', [], [], '', false);
        $this->resultRedirect = $this->getMock('Magento\Backend\Model\View\Result\Redirect', [], [], '', false);

        $this->resultFactory = $this->getMock('Magento\Framework\Controller\ResultFactory', [], [], '', false);
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);

        $this->context = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $this->context->expects($this->once())->method('getResultFactory')->willReturn($this->resultFactory);
        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
    }

    public function testSaveEmptyDataShouldRedirectToDefault()
    {
        $this->request->expects($this->once())->method('getPostValue')->willReturn([]);
        $this->resultRedirect->expects($this->once())->method('setPath')->with('adminhtml/*/')->willReturnSelf();

        $this->controller = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject('Magento\Sitemap\Controller\Adminhtml\Sitemap\Save', ['context' => $this->context]);

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    public function testTryToSaveInvalidDataShouldFailWithErrors()
    {
        $validatorClass = 'Magento\MediaStorage\Model\File\Validator\AvailablePath';
        $helperClass = 'Magento\Sitemap\Helper\Data';
        $validPaths = [];
        $messages = ['message1', 'message2'];
        $sessionClass = 'Magento\Backend\Model\Session';
        $data = ['sitemap_filename' => 'sitemap_filename', 'sitemap_path' => '/sitemap_path'];
        $siteMapId = 1;

        $this->request->expects($this->once())->method('getPostValue')->willReturn($data);
        $this->request->expects($this->once())->method('getParam')->with('sitemap_id')->willReturn($siteMapId);

        $validator = $this->getMock($validatorClass, [], [], '', false);
        $validator->expects($this->once())->method('setPaths')->with($validPaths);
        $validator->expects($this->once())
            ->method('isValid')
            ->with('/sitemap_path/sitemap_filename')
            ->willReturn(false);
        $validator->expects($this->once())->method('getMessages')->willReturn($messages);

        $helper = $this->getMock($helperClass, [], [], '', false);
        $helper->expects($this->once())->method('getValidPaths')->willReturn($validPaths);

        $session = $this->getMock($sessionClass, ['setFormData'], [], '', false);
        $session->expects($this->once())->method('setFormData')->with($data);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManager->expects($this->once())
            ->method('create')
            ->with($validatorClass)
            ->willReturn($validator);
        $objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([[$helperClass, $helper], [$sessionClass, $session]]);

        $messageManager = $this->getMock('Magento\Framework\Message\ManagerInterface', [], [], '', false);
        $messageManager->expects($this->at(0))->method('addError')->with($messages[0]);
        $messageManager->expects($this->at(1))->method('addError')->with($messages[1]);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/edit', ['sitemap_id' => $siteMapId])
            ->willReturnSelf();

        $this->context->expects($this->once())->method('getObjectManager')->willReturn($objectManager);
        $this->context->expects($this->once())->method('getMessageManager')->willReturn($messageManager);

        $this->controller = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject('Magento\Sitemap\Controller\Adminhtml\Sitemap\Save', ['context' => $this->context]);

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }
}
