<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Test\Unit\Controller\Adminhtml\Sitemap;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Controller\ResultFactory;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sitemap\Controller\Adminhtml\Sitemap\Save
     */
    protected $saveController;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPostValue'])
            ->getMockForAbstractClass();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->objectManagerHelper->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'resultFactory' => $this->resultFactoryMock,
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'objectManager' => $this->objectManagerMock
            ]
        );
        $this->saveController = $this->objectManagerHelper->getObject(
            \Magento\Sitemap\Controller\Adminhtml\Sitemap\Save::class,
            [
                'context' => $this->context
            ]
        );
    }

    public function testSaveEmptyDataShouldRedirectToDefault()
    {
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn([]);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->saveController->execute());
    }

    public function testTryToSaveInvalidDataShouldFailWithErrors()
    {
        $validatorClass = \Magento\MediaStorage\Model\File\Validator\AvailablePath::class;
        $helperClass = \Magento\Sitemap\Helper\Data::class;
        $validPaths = [];
        $messages = ['message1', 'message2'];
        $sessionClass = \Magento\Backend\Model\Session::class;
        $data = ['sitemap_filename' => 'sitemap_filename', 'sitemap_path' => '/sitemap_path'];
        $siteMapId = 1;

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('sitemap_id')
            ->willReturn($siteMapId);

        $validator = $this->getMock($validatorClass, [], [], '', false);
        $validator->expects($this->once())
            ->method('setPaths')
            ->with($validPaths)
            ->willReturnSelf();
        $validator->expects($this->once())
            ->method('isValid')
            ->with('/sitemap_path/sitemap_filename')
            ->willReturn(false);
        $validator->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $helper = $this->getMock($helperClass, [], [], '', false);
        $helper->expects($this->once())
            ->method('getValidPaths')
            ->willReturn($validPaths);

        $session = $this->getMock($sessionClass, ['setFormData'], [], '', false);
        $session->expects($this->once())
            ->method('setFormData')
            ->with($data)
            ->willReturnSelf();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($validatorClass)
            ->willReturn($validator);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([[$helperClass, $helper], [$sessionClass, $session]]);

        $this->messageManagerMock->expects($this->at(0))
            ->method('addError')
            ->withConsecutive(
                [$messages[0]],
                [$messages[1]]
            )
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/edit', ['sitemap_id' => $siteMapId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->saveController->execute());
    }
}
