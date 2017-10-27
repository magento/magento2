<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SaveTest
 * @package Magento\Cms\Test\Unit\Controller\Adminhtml\Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Cms\Model\BlockFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $blockFactory;

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $blockRepository;

    /**
     * @var \Magento\Cms\Controller\Adminhtml\Block\Save
     */
    private $saveController;

    /**
     * @var int
     */
    private $blockId = 1;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->resultRedirectFactory = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->resultRedirect =
            $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->resultRedirectFactory->expects($this->atLeastOnce())->method('create')->willReturn(
            $this->resultRedirect
        );
        $this->dataPersistorMock =
            $this->getMockBuilder(\Magento\Framework\App\Request\DataPersistorInterface::class)->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->setMethods(
            ['getParam', 'getPostValue']
        )->getMockForAbstractClass();
        $this->messageManagerMock =
            $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)->setMethods(
            ['dispatch']
        )->getMockForAbstractClass();
        $this->blockFactory =
            $this->getMockBuilder(\Magento\Cms\Model\BlockFactory::class)->disableOriginalConstructor()->setMethods(
                ['create']
            )->getMock();
        $this->blockRepository =
            $this->getMockBuilder(\Magento\Cms\Api\BlockRepositoryInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
        $this->saveController = $objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Block\Save::class,
            [
                'request'               => $this->requestMock,
                'messageManager'        => $this->messageManagerMock,
                'eventManager'          => $this->eventManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'dataPersistor'         => $this->dataPersistorMock,
                'blockFactory'          => $this->blockFactory,
                'blockRepository'       => $this->blockRepository,
            ]
        );
    }

    public function testSaveActionWithoutData()
    {
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(false);
        $this->requestMock->expects($this->never())->method('getParam');

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/')->willReturnSelf();
        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    /**
     * Test save existing block
     *
     * @dataProvider saveBlockDataProvider
     * @param $redirectBack
     * @param $redirectPath
     * @param $blockId
     */
    public function testSaveExistingBlockAction($redirectBack, $redirectPath, $blockId)
    {
        $postData = [
            'title'      => '"><img src=y onerror=prompt(document.domain)>;',
            'identifier' => 'unique_title_123',
            'stores'     => ['0'],
            'is_active'  => true,
            'content'    => '"><script>alert("cookie: "+document.cookie)</script>',
        ];
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->willReturnMap(
            [
                ['block_id', null, $blockId],
                ['back', null, $redirectBack],
            ]
        );
        $getIdExpectedTimes = $redirectBack ? 2 : 1;
        $block = $this->getMockBuilder(\Magento\Cms\Model\Block::class)->disableOriginalConstructor()->getMock();
        $block->expects($this->never())->method('load')->willReturnSelf();
        $block->expects($this->exactly($getIdExpectedTimes))->method('getId')->willReturn($blockId);
        $block->expects($this->once())->method('setData');
        $block->expects($this->never())->method('getData');

        $this->dataPersistorMock->expects($this->any())->method('clear')->with('cms_block');
        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with($redirectPath)->willReturnSelf();

        $this->blockFactory->expects($this->never())->method('create');
        $this->blockRepository->expects($this->once())->method('getById')->with($this->blockId)->willReturn($block);
        $this->blockRepository->expects($this->once())->method('save')->with($block);
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage')->with(
            __('You saved the block.')
        );
        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    /**
     * Test save existing block
     *
     * @dataProvider saveBlockDataProvider
     * @param $redirectBack
     * @param $redirectPath
     */
    public function testSaveNewBlockAction($redirectBack, $redirectPath)
    {
        $blockId = null;
        $postData = [
            'title'      => '"><img src=y onerror=prompt(document.domain)>;',
            'identifier' => 'unique_title_123',
            'stores'     => ['0'],
            'is_active'  => true,
            'content'    => '"><script>alert("cookie: "+document.cookie)</script>',
        ];
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->willReturnMap(
            [
                ['block_id', null, $blockId],
                ['back', null, $redirectBack],
            ]
        );
        $getIdExpectedTimes = $redirectBack ? 1 : 0;
        $block = $this->getMockBuilder(\Magento\Cms\Model\Block::class)->disableOriginalConstructor()->getMock();
        $block->expects($this->never())->method('load')->willReturnSelf();
        $block->expects($this->exactly($getIdExpectedTimes))->method('getId')->willReturn($blockId);
        $block->expects($this->once())->method('setData');
        $block->expects($this->never())->method('getData');

        $this->dataPersistorMock->expects($this->any())->method('clear')->with('cms_block');
        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with($redirectPath)->willReturnSelf();

        $this->blockFactory->expects($this->once())->method('create')->willReturn($block);
        $this->blockRepository->expects($this->never())->method('getById');
        $this->blockRepository->expects($this->once())->method('save')->with($block);
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage')->with(
            __('You saved the block.')
        );
        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    /**
     * Test try to save non existing block with id specified throws exception
     *
     * @dataProvider saveBlockDataProvider
     * @param $redirectBack
     */
    public function testSaveActionThrowsNoSuchEntityException($redirectBack)
    {
        $blockId = 7;
        $redirectPath = '*/*/';
        $postData = [
            'title'      => '"><img src=y onerror=prompt(document.domain)>;',
            'identifier' => 'unique_title_123',
            'stores'     => ['0'],
            'is_active'  => true,
            'content'    => '"><script>alert("cookie: "+document.cookie)</script>',
        ];
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn($postData);
        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->willReturnMap(
            [
                ['block_id', null, $blockId],
                ['back', null, $redirectBack],
            ]
        );

        $this->dataPersistorMock->expects($this->any())->method('clear')->with('cms_block');
        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with($redirectPath)->willReturnSelf();

        $this->blockFactory->expects($this->never())->method('create');
        $this->blockRepository->expects($this->once())->method('getById')->with($blockId)->willThrowException(
            new NoSuchEntityException(__('No such entity.'))
        );
        $this->blockRepository->expects($this->never())->method('save');
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage')->with(
            __('This block no longer exists.')
        );
        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    /**
     * Test save block data provider
     *
     * @return array
     */
    public function saveBlockDataProvider()
    {
        return [
            'save without redirect' => [false, '*/*/', $this->blockId],
            'save with redirect'    => [true, '*/*/edit', $this->blockId],
        ];
    }

    public function testSaveActionThrowsException()
    {
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(['block_id' => $this->blockId]);
        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->willReturnMap(
            [
                ['block_id', null, $this->blockId],
                ['back', null, true],
            ]
        );

        $block = $this->getMockBuilder(\Magento\Cms\Model\Block::class)->disableOriginalConstructor()->getMock();
        $block->expects($this->any())->method('load')->willReturnSelf();
        $block->expects($this->any())->method('getId')->willReturn(true);
        $block->expects($this->once())->method('setData');
        $block->expects($this->once())->method('getData')->willReturn(['block_id' => $this->blockId]);

        $this->blockFactory->expects($this->never())->method('create');

        $this->blockRepository->expects($this->once())->method('getById')->with($this->blockId)->willReturn($block);
        $this->blockRepository->expects($this->once())->method('save')->with($block)->willThrowException(
            new \Exception('Error message.')
        );

        $this->messageManagerMock->expects($this->never())->method('addSuccessMessage');
        $this->messageManagerMock->expects($this->once())->method('addExceptionMessage');

        $this->dataPersistorMock->expects($this->any())->method('set')->with(
            'cms_block',
            ['block_id' => $this->blockId]
        );

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with(
            '*/*/edit',
            ['block_id' => $this->blockId]
        )->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }
}
