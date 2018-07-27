<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\Edit
     */
    protected $editController;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\ManagerInterface', [], [], '', false);
        $this->coreRegistryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);

        $this->pageMock = $this->getMockBuilder('Magento\Cms\Model\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')
            ->setMethods(['create', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Cms\Model\Page')
            ->willReturn($this->pageMock);

        $this->resultRedirectMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactoryMock = $this->getMock('Magento\Framework\View\Result\PageFactory', [], [], '', false);

        $this->requestMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->contextMock = $this->getMock(
            '\Magento\Backend\App\Action\Context',
            [],
            [],
            '',
            false
        );
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->editController = $this->objectManager->getObject(
            'Magento\Cms\Controller\Adminhtml\Page\Edit',
            [
                'context' => $this->contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'registry' => $this->coreRegistryMock,
            ]
        );
    }

    public function testEditActionPageNoExists()
    {
        $pageId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('page_id')
            ->willReturn($pageId);

        $this->pageMock->expects($this->once())
            ->method('load')
            ->with($pageId);
        $this->pageMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('This page no longer exists.'));

        $this->resultRedirectFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->editController->execute());
    }

    /**
     * @param int $pageId
     * @param string $label
     * @param string $title
     * @dataProvider editActionData
     */
    public function testEditAction($pageId, $label, $title)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('page_id')
            ->willReturn($pageId);

        $this->pageMock->expects($this->any())
            ->method('load')
            ->with($pageId);
        $this->pageMock->expects($this->any())
            ->method('getId')
            ->willReturn($pageId);
        $this->pageMock->expects($this->any())
            ->method('getTitle')
            ->willReturn('Test title');

        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with('cms_page', $this->pageMock);

        $resultPageMock = $this->getMock('Magento\Backend\Model\View\Result\Page', [], [], '', false);

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $titleMock = $this->getMock('Magento\Framework\View\Page\Title', [], [], '', false);
        $titleMock->expects($this->at(0))->method('prepend')->with(__('Pages'));
        $titleMock->expects($this->at(1))->method('prepend')->with($this->getTitle());
        $pageConfigMock = $this->getMock('Magento\Framework\View\Page\Config', [], [], '', false);
        $pageConfigMock->expects($this->exactly(2))->method('getTitle')->willReturn($titleMock);

        $resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->willReturnSelf();
        $resultPageMock->expects($this->any())
            ->method('addBreadcrumb')
            ->willReturnSelf();
        $resultPageMock->expects($this->at(3))
            ->method('addBreadcrumb')
            ->with(__($label), __($title))
            ->willReturnSelf();
        $resultPageMock->expects($this->exactly(2))
            ->method('getConfig')
            ->willReturn($pageConfigMock);

        $this->assertSame($resultPageMock, $this->editController->execute());
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    protected function getTitle()
    {
        return $this->pageMock->getId() ? $this->pageMock->getTitle() : __('New Page');
    }

    /**
     * @return array
     */
    public function editActionData()
    {
        return [
            [null, 'New Page', 'New Page'],
            [2, 'Edit Page', 'Edit Page']
        ];
    }
}
