<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Test\Unit\Controller\Adminhtml\Sitemap;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sitemap\Controller\Adminhtml\Sitemap\Save;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sitemap\Controller\Adminhtml\Sitemap\Save
     */
    private $saveController;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\Validator\StringLength|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lengthValidator;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\AvailablePath|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pathValidator;

    /**
     * @var \Magento\Sitemap\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileSystem;

    /**
     * @var \Magento\Sitemap\Model\SitemapFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $siteMapFactory;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->helper = $this->getMockBuilder(\Magento\Sitemap\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);
        $this->session = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFormData'])
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->contextMock->expects($this->once())
            ->method('getSession')
            ->willReturn($this->session);

        $this->lengthValidator = $this->getMockBuilder(\Magento\Framework\Validator\StringLength::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pathValidator =
            $this->getMockBuilder(\Magento\MediaStorage\Model\File\Validator\AvailablePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->siteMapFactory = $this->createMock(\Magento\Sitemap\Model\SitemapFactory::class);

        $this->saveController = new Save(
            $this->contextMock,
            $this->lengthValidator,
            $this->pathValidator,
            $this->helper,
            $this->fileSystem,
            $this->siteMapFactory
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
        $validPaths = [];
        $messages = ['message1', 'message2'];
        $data = ['sitemap_filename' => 'sitemap_filename', 'sitemap_path' => '/sitemap_path'];
        $siteMapId = 1;

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('sitemap_id')
            ->willReturn($siteMapId);

        $this->pathValidator->expects($this->once())
            ->method('setPaths')
            ->with($validPaths)
            ->willReturnSelf();
        $this->pathValidator->expects($this->once())
            ->method('isValid')
            ->with('/sitemap_path/sitemap_filename')
            ->willReturn(false);
        $this->pathValidator->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $this->helper->expects($this->once())
            ->method('getValidPaths')
            ->willReturn($validPaths);

        $this->session->expects($this->once())
            ->method('setFormData')
            ->with($data)
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->at(0))
            ->method('addErrorMessage')
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

    public function testTryToSaveInvalidFileNameShouldFailWithErrors()
    {
        $validPaths = [];
        $messages = ['message1', 'message2'];
        $data = ['sitemap_filename' => 'sitemap_filename', 'sitemap_path' => '/sitemap_path'];
        $siteMapId = 1;

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('sitemap_id')
            ->willReturn($siteMapId);

        $this->lengthValidator->expects($this->once())
            ->method('isValid')
            ->with('sitemap_filename')
            ->willReturn(false);
        $this->lengthValidator->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $this->pathValidator->expects($this->once())
            ->method('setPaths')
            ->with($validPaths)
            ->willReturnSelf();
        $this->pathValidator->expects($this->once())
            ->method('isValid')
            ->with('/sitemap_path/sitemap_filename')
            ->willReturn(true);

        $this->helper->expects($this->once())
            ->method('getValidPaths')
            ->willReturn($validPaths);

        $this->session->expects($this->once())
            ->method('setFormData')
            ->with($data)
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->at(0))
            ->method('addErrorMessage')
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
