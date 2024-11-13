<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Cms\Controller\Adminhtml\Block\Edit;
use Magento\Cms\Model\Block;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends TestCase
{
    /**
     * @var Edit
     */
    protected $editController;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Cms\Model\Block|MockObject
     */
    protected $blockMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Registry|MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->coreRegistryMock = $this->createMock(Registry::class);

        $this->blockMock = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->onlyMethods(['create', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Block::class)
            ->willReturn($this->blockMock);

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);

        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->editController = $this->objectManager->getObject(
            Edit::class,
            [
                'context' => $this->contextMock,
                'coreRegistry' => $this->coreRegistryMock,
                'resultPageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testEditActionBlockNoExists(): void
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
     * @param int|null $blockId
     * @param string $label
     * @param string $title
     *
     * @return void
     * @dataProvider editActionData
     */
    public function testEditAction(?int $blockId, string $label, string $title): void
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

        $resultPageMock = $this->createMock(Page::class);

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $titleMock = $this->createMock(Title::class);
        $titleMock
            ->method('prepend')
            ->willReturnCallback(function ($arg) {
                if ($arg == $this->getTitle() || $arg == [__('Blocks')]) {
                    return null;
                }
            });
        $pageConfigMock = $this->createMock(Config::class);
        $pageConfigMock->expects($this->exactly(2))->method('getTitle')->willReturn($titleMock);

        $resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->willReturnSelf();
        $resultPageMock->expects($this->any())
            ->method('addBreadcrumb')
            ->willReturnSelf();
        $resultPageMock
            ->method('addBreadcrumb')
            ->willReturnCallback(function ($arg1, $arg2) use ($label, $title, $resultPageMock) {
                if ($arg1 == (__($label)) || $arg1 == (__($title))) {
                    return $resultPageMock;
                } elseif ($arg1 === null && $arg2 === null) {
                    return null;
                }
            });
        $resultPageMock->expects($this->exactly(2))
            ->method('getConfig')
            ->willReturn($pageConfigMock);

        $this->assertSame($resultPageMock, $this->editController->execute());
    }

    /**
     * @return Phrase|string
     */
    protected function getTitle()
    {
        return $this->blockMock->getId() ? $this->blockMock->getTitle() : __('New Block');
    }

    /**
     * @return array
     */
    public static function editActionData(): array
    {
        return [
            [null, 'New Block', 'New Block'],
            [2, 'Edit Block', 'Edit Block']
        ];
    }
}
