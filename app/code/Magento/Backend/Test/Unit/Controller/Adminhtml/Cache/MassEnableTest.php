<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Cache;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Backend\Controller\Adminhtml\Cache\MassEnable;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\State;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;
use Magento\Framework\App\Cache\StateInterface as CacheState;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassEnableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MassEnable
     */
    private $controller;

    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var MessageManager|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Redirect|MockObject
     */
    private $redirectMock;

    /**
     * @var Request|MockObject
     */
    private $requestMock;

    /**
     * @var CacheTypeList|MockObject
     */
    private $cacheTypeListMock;

    /**
     * @var CacheState|MockObject
     */
    private $cacheStateMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(MessageManager::class)
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->getMockForAbstractClass();

        $this->cacheTypeListMock = $this->getMockBuilder(CacheTypeList::class)
            ->getMockForAbstractClass();

        $this->cacheStateMock = $this->getMockBuilder(CacheState::class)
            ->getMockForAbstractClass();

        $this->redirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*')
            ->willReturnSelf();
        $resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
        $resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirectMock);

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
        $contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($resultFactoryMock);
        $contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->controller = $objectManagerHelper->getObject(
            MassEnable::class,
            [
                'context' => $contextMock,
                'cacheTypeList' => $this->cacheTypeListMock,
                'cacheState' => $this->cacheStateMock
            ]
        );
        $objectManagerHelper->setBackwardCompatibleProperty($this->controller, 'state', $this->stateMock);
    }

    public function testExecuteInProductionMode()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('You can\'t change status of cache type(s) in production mode', null)
            ->willReturnSelf();

        $this->assertSame($this->redirectMock, $this->controller->execute());
    }

    public function testExecuteInvalidTypeCache()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->cacheTypeListMock->expects($this->once())
            ->method('getTypes')
            ->willReturn([
                'pageCache' => [
                    'id' => 'pageCache',
                    'label' => 'Cache of Page'
                ]
            ]);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('types')
            ->willReturn(['someCache']);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('These cache type(s) don\'t exist: someCache')
            ->willReturnSelf();

        $this->assertSame($this->redirectMock, $this->controller->execute());
    }

    public function testExecuteWithException()
    {
        $exception = new \Exception();

        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, 'An error occurred while enabling cache.')
            ->willReturnSelf();

        $this->assertSame($this->redirectMock, $this->controller->execute());
    }

    public function testExecuteSuccess()
    {
        $cacheType = 'pageCache';

        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->cacheTypeListMock->expects($this->once())
            ->method('getTypes')
            ->willReturn([
                'pageCache' => [
                    'id' => 'pageCache',
                    'label' => 'Cache of Page'
                ]
            ]);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('types')
            ->willReturn([$cacheType]);

        $this->cacheStateMock->expects($this->once())
            ->method('isEnabled')
            ->with($cacheType)
            ->willReturn(false);
        $this->cacheStateMock->expects($this->once())
            ->method('setEnabled')
            ->with($cacheType, true);
        $this->cacheStateMock->expects($this->once())
            ->method('persist');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('1 cache type(s) enabled.')
            ->willReturnSelf();

        $this->assertSame($this->redirectMock, $this->controller->execute());
    }
}
