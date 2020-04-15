<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Block\Edit
     */
    protected $editController;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Cms\Model\Block|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $blockMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageFactoryMock;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->coreRegistryMock = $this->createMock(\Magento\Framework\Registry::class);

        $this->blockMock = $this->getMockBuilder(\Magento\Cms\Model\Block::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->setMethods(['create', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Cms\Model\Block::class)
            ->willReturn($this->blockMock);

        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class
        )->disableOriginalConstructor()->getMock();

        $this->resultPageFactoryMock = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);

        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->editController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Block\Edit::class,
            [
                'context' => $this->contextMock,
                'coreRegistry' => $this->coreRegistryMock,
                'resultPageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    public function testEditActionBlockNoExists()
    {
        $blockId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('block_id')
            ->willReturn($blockId);

        $this->blockMock->expects($this->once())
            ->method('load')
            ->with($blockId);
        $this->blockMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This block no longer exists.'));

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
     * @param int $blockId
     * @param string $label
     * @param string $title
     * @dataProvider editActionData
     */
    public function testEditAction($blockId, $label, $title)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('block_id')
            ->willReturn($blockId);

        $this->blockMock->expects($this->any())
            ->method('load')
            ->with($blockId);
        $this->blockMock->expects($this->any())
            ->method('getId')
            ->willReturn($blockId);
        $this->blockMock->expects($this->any())
            ->method('getTitle')
            ->willReturn('Test title');

        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with('cms_block', $this->blockMock);

        $resultPageMock = $this->createMock(\Magento\Backend\Model\View\Result\Page::class);

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $titleMock = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $titleMock->expects($this->at(0))->method('prepend')->with(__('Blocks'));
        $titleMock->expects($this->at(1))->method('prepend')->with($this->getTitle());
        $pageConfigMock = $this->createMock(\Magento\Framework\View\Page\Config::class);
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
        return $this->blockMock->getId() ? $this->blockMock->getTitle() : __('New Block');
    }

    /**
     * @return array
     */
    public function editActionData()
    {
        return [
            [null, 'New Block', 'New Block'],
            [2, 'Edit Block', 'Edit Block']
        ];
    }
}
