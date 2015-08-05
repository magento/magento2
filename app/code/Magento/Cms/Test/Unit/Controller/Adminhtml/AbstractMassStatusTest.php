<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml;

class AbstractMassStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\AbstractMassStatus
     */
    protected $abstractMassStatusController;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @var \Magento\Framework\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelMock;

    /**
     * @var bool
     */
    protected $status = true;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\ManagerInterface', [], [], '', false);

        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);

        $this->collectionMock = $this->getMockBuilder(
            'Magento\Framework\Model\Resource\Db\Collection\AbstractCollection'
        )->disableOriginalConstructor()->getMock();

        $this->modelMock = $this->getMockBuilder('Magento\Framework\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load', 'setIsActive', 'save'
                ]
            )
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            ['getParam']
        );

        $this->resultRedirect = $this->getMock('Magento\Backend\Model\View\Result\Redirect', [], [], '', false);
        $this->resultFactory = $this->getMock('Magento\Framework\Controller\ResultFactory', ['create'], [], '', false);
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);

        $this->contextMock = $this->getMock('\Magento\Backend\App\Action\Context', [], [], '', false);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())->method('getResultFactory')->willReturn($this->resultFactory);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);

        $this->abstractMassStatusController = $this->objectManager->getObject(
            'Magento\Cms\Controller\Adminhtml\AbstractMassStatus',
            [
                'context' => $this->contextMock,
            ]
        );
    }

    /**
     * @param array $items
     *
     * @dataProvider getPageIdsWithSetSelectAllDataProvider
     */
    public function testExecuteWithSetStatusAll(array $items)
    {
        $requestParams = [
            ['excluded', null, false],
            ['selected', null, null]
        ];

        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->willReturnMap($requestParams);

        $this->objectManagerMock->expects($this->once())->method('get')->willReturn($this->collectionMock);
        $this->objectManagerMock->expects($this->atLeastOnce())->method('create')->willReturn($this->modelMock);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($items);

        $this->modelMock->expects($this->atLeastOnce())->method('load')->willReturnSelf();
        $this->modelMock->expects($this->atLeastOnce())->method('setIsActive')->with($this->status)->willReturnSelf();
        $this->modelMock->expects($this->atLeastOnce())->method('save')->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->abstractMassStatusController->execute());
    }

    /**
     * @param array $items
     *
     * @dataProvider getPageIdsWithSelectedSetStatusDataProvider
     */
    public function testExecuteWithSelectedSetStatus(array $items)
    {
        $requestParams = [
            ['excluded', null, null],
            ['selected', null, ['1', '7']]
        ];

        $selectedIds = ['1', '7'];

        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->willReturnMap($requestParams);

        $this->objectManagerMock->expects($this->once())->method('get')->willReturn($this->collectionMock);
        $this->objectManagerMock->expects($this->atLeastOnce())->method('create')->willReturn($this->modelMock);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())->method('addFieldToFilter')
            ->with('entity_id', ['in' => $selectedIds])
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($items);

        $this->modelMock->expects($this->atLeastOnce())->method('load')->willReturnSelf();
        $this->modelMock->expects($this->atLeastOnce())->method('setIsActive')->with($this->status)->willReturnSelf();
        $this->modelMock->expects($this->atLeastOnce())->method('save')->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->abstractMassStatusController->execute());
    }

    /**
     * @param array $items
     *
     * @dataProvider getPageIdsWithExcludedSetStatusDataProvider
     */
    public function testExecuteWithExcludedSetStatus(array $items)
    {
        $requestParams = [
            ['excluded', null, ['1', '3', '5']],
            ['selected', null, null]
        ];

        $excludedIds = ['1', '3', '5'];

        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->willReturnMap($requestParams);

        $this->objectManagerMock->expects($this->once())->method('get')->willReturn($this->collectionMock);
        $this->objectManagerMock->expects($this->atLeastOnce())->method('create')->willReturn($this->modelMock);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())->method('addFieldToFilter')
            ->with('entity_id', ['nin' => $excludedIds])
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($items);

        $this->modelMock->expects($this->atLeastOnce())->method('load')->willReturnSelf();
        $this->modelMock->expects($this->atLeastOnce())->method('setIsActive')->with($this->status)->willReturnSelf();
        $this->modelMock->expects($this->atLeastOnce())->method('save')->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->abstractMassStatusController->execute());
    }

    public function testExecuteWithNoItemsSelected()
    {
        $requestParams = [
            ['excluded', null, null],
            ['selected', null, null]
        ];

        $phrase = new \Magento\Framework\Phrase('Please select item(s).');

        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->willReturnMap($requestParams);

        $this->messageManagerMock->expects($this->once())->method('addError')->with($phrase)->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->abstractMassStatusController->execute());
    }

    public function testExecuteThrowsException()
    {
        $requestParams = [
            ['excluded', null, false],
            ['selected', null, null]
        ];

        $this->requestMock->expects($this->atLeastOnce())->method('getParam')->willReturnMap($requestParams);

        $this->objectManagerMock->expects($this->atLeastOnce())->method('get')->willThrowException(new \Exception());

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->abstractMassStatusController->execute());
    }

    /**
     * @return array
     */
    public function getPageIdsWithSetSelectAllDataProvider()
    {
        return [
            [
                ['3', '2', '1', '4', '7', '6', '5']
            ]
        ];
    }

    /**
     * @return array
     */
    public function getPageIdsWithSelectedSetStatusDataProvider()
    {
        return [
            [
                ['1', '7']
            ]
        ];
    }

    /**
     * @return array
     */
    public function getPageIdsWithExcludedSetStatusDataProvider()
    {
        return [
            [
                ['1', '3', '5']
            ]
        ];
    }
}
