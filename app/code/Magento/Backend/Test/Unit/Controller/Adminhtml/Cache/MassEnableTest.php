<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

class MassEnableTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(MessageManager::class)
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

        $this->controller = $objectManagerHelper->getObject(
            MassEnable::class,
            ['context' => $contextMock]
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

    //public function testExecute
}
