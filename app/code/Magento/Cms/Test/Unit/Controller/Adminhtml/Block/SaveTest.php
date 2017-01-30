<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataPersistorMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Cms\Model\Block|\PHPUnit_Framework_MockObject_MockObject $blockMock
     */
    protected $blockMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var int
     */
    protected $blockId = 1;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);

        $this->resultRedirectFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->dataPersistorMock = $this->getMockBuilder('Magento\Framework\App\Request\DataPersistorInterface')
            ->getMock();

        $this->requestMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPostValue']
        );

        $this->blockMock = $this->getMockBuilder('Magento\Cms\Model\Block')->disableOriginalConstructor()->getMock();

        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\ManagerInterface', [], [], '', false);

        $this->eventManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );

        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')
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

        $this->saveController = $this->objectManager->getObject(
            'Magento\Cms\Controller\Adminhtml\Block\Save',
            [
                'context' => $this->contextMock,
                'dataPersistor' => $this->dataPersistorMock,
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
        'content' => '"><script>alert("cookie: "+document.cookie)</script>'
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

        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->with($this->equalTo('Magento\Cms\Model\Block'))
            ->willReturn($this->blockMock);

        $this->blockMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->blockMock->expects($this->any())
            ->method('getId')
            ->willReturn(true);
        $this->blockMock->expects($this->once())->method('setData');
        $this->blockMock->expects($this->once())->method('save');

        $this->dataPersistorMock->expects($this->any())
            ->method('clear')
            ->with('cms_block');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the block.'));

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/') ->willReturnSelf();

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
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(['block_id' => 1]);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['block_id', null, 1],
                    ['back', null, false],
                ]
            );

        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->with($this->equalTo('Magento\Cms\Model\Block'))
            ->willReturn($this->blockMock);

        $this->blockMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->blockMock->expects($this->any())
            ->method('getId')
            ->willReturn(false);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('This block no longer exists.'));

        $this->resultRedirect->expects($this->atLeastOnce())->method('setPath')->with('*/*/') ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveAndContinue()
    {
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(['block_id' => 1]);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['block_id', null, 1],
                    ['back', null, true],
                ]
            );

        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->with($this->equalTo('Magento\Cms\Model\Block'))
            ->willReturn($this->blockMock);

        $this->blockMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->blockMock->expects($this->any())
            ->method('getId')
            ->willReturn(true);
        $this->blockMock->expects($this->once())->method('setData');
        $this->blockMock->expects($this->once())->method('save');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the block.'));

        $this->dataPersistorMock->expects($this->any())
            ->method('clear')
            ->with('cms_block');

        $this->resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('*/*/edit', ['block_id' => $this->blockId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }

    public function testSaveActionThrowsException()
    {
        $this->requestMock->expects($this->any())->method('getPostValue')->willReturn(['block_id' => 1]);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['block_id', null, 1],
                    ['back', null, true],
                ]
            );

        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->with($this->equalTo('Magento\Cms\Model\Block'))
            ->willReturn($this->blockMock);

        $this->blockMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->blockMock->expects($this->any())
            ->method('getId')
            ->willReturn(true);
        $this->blockMock->expects($this->once())->method('setData');
        $this->blockMock->expects($this->once())->method('save')->willThrowException(new \Exception('Error message.'));

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');
        $this->messageManagerMock->expects($this->once())
            ->method('addException');

        $this->dataPersistorMock->expects($this->any())
            ->method('set')
            ->with('cms_block', ['block_id' => 1]);

        $this->resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('*/*/edit', ['block_id' => $this->blockId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->saveController->execute());
    }
}
