<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Controller\Adminhtml\Block\Save;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var DataPersistorInterface|MockObject
     */
    protected $dataPersistorMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Block|MockObject $blockMock
     */
    protected $blockMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var Save
     */
    protected $saveController;

    /**
     * @var BlockFactory|MockObject
     */
    private $blockFactory;

    /**
     * @var BlockRepositoryInterface|MockObject
     */
    private $blockRepository;

    /**
     * @var int
     */
    protected $blockId = 1;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->createMock(Context::class);

        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->getMock();

        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPostValue']
        );

        $this->blockMock = $this->getMockBuilder(
            Block::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->eventManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );

        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'create'])
            ->getMock();

        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->blockFactory = $this->getMockBuilder(BlockFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->blockRepository = $this->getMockBuilder(BlockRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->saveController = $this->objectManager->getObject(
            Save::class,
            [
                'context' => $this->contextMock,
                'dataPersistor' => $this->dataPersistorMock,
                'blockFactory' => $this->blockFactory,
                'blockRepository' => $this->blockRepository
            ]
        );
    }

    /**
     * @return void
     */
    public function testSaveAction(): void
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
                    ['back', null, 'continue']
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

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/edit')->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    /**
     * @return void
     */
    public function testSaveActionWithoutData(): void
    {
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(false);
        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/')->willReturnSelf();
        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    /**
     * @return void
     */
    public function testSaveActionNoId(): void
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
                    ['back', null, false]
                ]
            );

        $this->blockFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->blockMock);

        $this->blockRepository->expects($this->once())
            ->method('getById')
            ->with($this->blockId)
            ->willThrowException(new NoSuchEntityException(__('Error message')));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This block no longer exists.'));

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/')->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    /**
     * @return void
     */
    public function testSaveAndDuplicate(): void
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
                    ['back', null, true]
                ]
            );

        $duplicateBlockMock = $this->getMockBuilder(
            Block::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->blockFactory
            ->method('create')
            ->willReturnOnConsecutiveCalls($this->blockMock, $duplicateBlockMock);

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
        $this->blockRepository
            ->method('save')
            ->withConsecutive([$this->blockMock], [$duplicateBlockMock]);

        $this->messageManagerMock
            ->method('addSuccessMessage')
            ->withConsecutive([__('You saved the block.')], [__('You duplicated the block.')]);

        $this->dataPersistorMock->expects($this->any())
            ->method('clear')
            ->with('cms_block');

        $this->resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('*/*/edit', ['block_id' => $this->blockId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    /**
     * @return void
     */
    public function testSaveAndClose(): void
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
                    ['back', null, 'close']
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

    /**
     * @return void
     */
    public function testSaveActionWithMarginalSpace(): void
    {
        $postData = [
            'title' => 'unique_title_123',
            'identifier' => '  unique_title_123',
            'stores' => ['0'],
            'is_active' => true,
            'content' => '',
            'back' => 'continue'
        ];

        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['block_id', null, 1],
                    ['back', null, true]
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
            ->willThrowException(new \Exception('No marginal white space please.'));

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

    /**
     * @return void
     */
    public function testSaveActionThrowsException(): void
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
                    ['back', null, true]
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
