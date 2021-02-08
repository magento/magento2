<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataPersistorMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Cms\Model\Block|\PHPUnit\Framework\MockObject\MockObject $blockMock
     */
    protected $blockMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Cms\Controller\Adminhtml\Block\Save
     */
    protected $saveController;

    /**
     * @var \Magento\Cms\Model\BlockFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $blockFactory;

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $blockRepository;

    /**
     * @var int
     */
    protected $blockId = 1;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);

        $this->resultRedirectFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->dataPersistorMock = $this->getMockBuilder(\Magento\Framework\App\Request\DataPersistorInterface::class)
            ->getMock();

        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPostValue']
        );

        $this->blockMock = $this->getMockBuilder(
            \Magento\Cms\Model\Block::class
        )->disableOriginalConstructor()->getMock();

        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->eventManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();

        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->blockFactory = $this->getMockBuilder(\Magento\Cms\Model\BlockFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->blockRepository = $this->getMockBuilder(\Magento\Cms\Api\BlockRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->saveController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Block\Save::class,
            [
                'context' => $this->contextMock,
                'dataPersistor' => $this->dataPersistorMock,
                'blockFactory' => $this->blockFactory,
                'blockRepository' => $this->blockRepository,
            ]
        );
    }

    public function testSaveAction()
    {
        $postData = [
            'title' => '"><img src=y onerror=prompt(document.domain)>;',
            'identifier' => 'unique_title_123',
            'stores' => ['0'],
            'is_active' => true,
            'content' => '"><script>alert("cookie: "+document.cookie)</script>',
            'back' => 'continue'
        ];

        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['block_id', null, 1],
                    ['back', null, 'continue'],
                ]
            );

        $this->blockFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->blockMock);

        $this->blockRepository->expects($this->once())
            ->method('getById')
            ->with($this->blockId)
            ->willReturn($this->blockMock);

        $this->blockMock->expects($this->once())->method('setData');
        $this->blockRepository->expects($this->once())->method('save')->with($this->blockMock);

        $this->dataPersistorMock->expects($this->any())
            ->method('clear')
            ->with('cms_block');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the block.'));

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/edit') ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveActionWithoutData()
    {
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(false);
        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/') ->willReturnSelf();
        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveActionNoId()
    {
        $postData = [
            'block_id' => 1,
            'back' => 'continue'
        ];

        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['block_id', null, 1],
                    ['back', null, false],
                ]
            );

        $this->blockFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->blockMock);

        $this->blockRepository->expects($this->once())
            ->method('getById')
            ->with($this->blockId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('Error message')));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This block no longer exists.'));

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/') ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveAndDuplicate()
    {
        $postData = [
            'title' => 'unique_title_123',
            'identifier' => 'unique_title_123',
            'stores' => ['0'],
            'is_active' => true,
            'content' => '"><script>alert("cookie: "+document.cookie)</script>',
            'back' => 'duplicate'
        ];

        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['block_id', null, 1],
                    ['back', null, true],
                ]
            );

        $this->blockFactory->expects($this->at(0))
            ->method('create')
            ->willReturn($this->blockMock);

        $duplicateBlockMock = $this->getMockBuilder(
            \Magento\Cms\Model\Block::class
        )->disableOriginalConstructor()->getMock();

        $this->blockFactory->expects($this->at(1))
            ->method('create')
            ->willReturn($duplicateBlockMock);

        $duplicateBlockMock->expects($this->atLeastOnce())
            ->method('setId')
            ->with(null)
            ->willReturnSelf();

        $duplicateBlockMock->expects($this->atLeastOnce())
            ->method('setIdentifier')
            ->willReturnSelf();

        $duplicateBlockMock->expects($this->atLeastOnce())
            ->method('setIsActive')
            ->with(0)
            ->willReturnSelf();

        $duplicateBlockMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->blockRepository->expects($this->once())
            ->method('getById')
            ->with($this->blockId)
            ->willReturn($this->blockMock);

        $this->blockMock->expects($this->any())->method('setData');
        $this->blockRepository->expects($this->at(1))->method('save')->with($this->blockMock);
        $this->blockRepository->expects($this->at(2))->method('save')->with($duplicateBlockMock);

        $this->messageManagerMock->expects($this->at(0))
            ->method('addSuccessMessage')
            ->with(__('You saved the block.'));

        $this->messageManagerMock->expects($this->at(1))
            ->method('addSuccessMessage')
            ->with(__('You duplicated the block.'));

        $this->dataPersistorMock->expects($this->any())
            ->method('clear')
            ->with('cms_block');

        $this->resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('*/*/edit', ['block_id' => $this->blockId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveAndClose()
    {
        $postData = [
            'title' => '"><img src=y onerror=prompt(document.domain)>;',
            'identifier' => 'unique_title_123',
            'stores' => ['0'],
            'is_active' => true,
            'content' => '"><script>alert("cookie: "+document.cookie)</script>',
            'back' => 'close'
        ];

        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['block_id', null, 1],
                    ['back', null, 'close'],
                ]
            );

        $this->blockFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->blockMock);

        $this->blockRepository->expects($this->atLeastOnce())
            ->method('getById')
            ->with($this->blockId)
            ->willReturn($this->blockMock);

        $this->blockMock->expects($this->atLeastOnce())->method('setData');
        $this->blockRepository->expects($this->once())->method('save')->with($this->blockMock);

        $this->dataPersistorMock->expects($this->any())
            ->method('clear')
            ->with('cms_block');

        $this->messageManagerMock->expects($this->atLeastOnce())
            ->method('addSuccessMessage')
            ->with(__('You saved the block.'));

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/')->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveActionThrowsException()
    {
        $postData = [
            'title' => '"><img src=y onerror=prompt(document.domain)>;',
            'identifier' => 'unique_title_123',
            'stores' => ['0'],
            'is_active' => true,
            'content' => '"><script>alert("cookie: "+document.cookie)</script>',
            'back' => 'continue'
        ];

        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['block_id', null, 1],
                    ['back', null, true],
                ]
            );

        $this->blockFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->blockMock);

        $this->blockRepository->expects($this->once())
            ->method('getById')
            ->with($this->blockId)
            ->willReturn($this->blockMock);

        $this->blockMock->expects($this->once())->method('setData');
        $this->blockRepository->expects($this->once())->method('save')
            ->with($this->blockMock)
            ->willThrowException(new \Exception('Error message.'));

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage');

        $this->dataPersistorMock->expects($this->any())
            ->method('set')
            ->with('cms_block', array_merge($postData, ['block_id' => null]));

        $this->resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('*/*/edit', ['block_id' => $this->blockId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }
}
