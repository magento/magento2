<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Adapter;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ManagerInterface
     */
    private $eventManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ValidatorPoolInterface
     */
    private $validatorPool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $formBlockType;

    /**
     * @var string
     */
    private $infoBlockType;

    /**
     * @var Adapter
     */
    private $adapter;

    protected function setUp()
    {
        $this->eventManager = $this->getMock(
            'Magento\Framework\Event\ManagerInterface'
        );
        $this->valueHandlerPool = $this->getMock(
            'Magento\Payment\Gateway\Config\ValueHandlerPoolInterface'
        );
        $this->validatorPool = $this->getMock(
            'Magento\Payment\Gateway\Validator\ValidatorPoolInterface'
        );
        $this->commandPool = $this->getMock(
            'Magento\Payment\Gateway\Command\CommandPoolInterface'
        );
        $this->paymentDataObjectFactory = $this->getMockBuilder(
            'Magento\Payment\Gateway\Data\PaymentDataObjectFactory'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->code = 'CODE';
        $this->formBlockType = '\FormBlock';
        $this->infoBlockType = '\InfoBlock';

        $this->adapter = new Adapter(
            $this->eventManager,
            $this->valueHandlerPool,
            $this->paymentDataObjectFactory,
            $this->code,
            $this->formBlockType,
            $this->infoBlockType,
            $this->commandPool,
            $this->validatorPool
        );
    }

    public function testIsAvailableNotActive()
    {
        $activeValueHandler = $this->getMock(
            'Magento\Payment\Gateway\Config\ValueHandlerInterface'
        );

        $this->valueHandlerPool->expects(static::once())
            ->method('get')
            ->with('active')
            ->willReturn($activeValueHandler);
        $activeValueHandler->expects(static::once())
            ->method('handle')
            ->with(['field' => 'active'])
            ->willReturn(false);

        $this->eventManager->expects(static::never())
            ->method('dispatch');

        static::assertFalse($this->adapter->isAvailable(null));
    }

    public function testIsAvailableEmptyQuote()
    {
        $activeValueHandler = $this->getMock(
            'Magento\Payment\Gateway\Config\ValueHandlerInterface'
        );
        $availabilityValidator = $this->getMock(
            'Magento\Payment\Gateway\Validator\ValidatorInterface'
        );
        $paymentDO = $this->getMock('Magento\Payment\Gateway\Data\PaymentDataObjectInterface');
        $validationResult = $this->getMock('Magento\Payment\Gateway\Validator\ResultInterface');
        $paymentInfo = $this->getMock('Magento\Payment\Model\InfoInterface');

        $this->valueHandlerPool->expects(static::once())
            ->method('get')
            ->with('active')
            ->willReturn($activeValueHandler);
        $activeValueHandler->expects(static::once())
            ->method('handle')
            ->with(['field' => 'active', 'payment' => $paymentDO])
            ->willReturn(true);

        $this->validatorPool->expects(static::once())
            ->method('get')
            ->with('availability')
            ->willReturn($availabilityValidator);
        $this->paymentDataObjectFactory->expects(static::exactly(2))
            ->method('create')
            ->with($paymentInfo)
            ->willReturn($paymentDO);
        $availabilityValidator->expects(static::once())
            ->method('validate')
            ->willReturn($validationResult);
        $validationResult->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->eventManager->expects(static::once())
            ->method('dispatch');

        $this->adapter->setInfoInstance($paymentInfo);
        static::assertTrue($this->adapter->isAvailable(null));
    }

    public function testExecuteCommandWithCommandExecutor()
    {
        /** @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->getMock(
            ManagerInterface::class
        );

        /** @var ValueHandlerPoolInterface|\PHPUnit_Framework_MockObject_MockObject $valueHandlerPool */
        $valueHandlerPool = $this->getMock(
            ValueHandlerPoolInterface::class
        );

        /** @var CommandManagerInterface|\PHPUnit_Framework_MockObject_MockObject $commandManager */
        $commandManager = $this->getMock(
            CommandManagerInterface::class
        );

        /** @var PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject $paymentDataObjectFactory */
        $paymentDataObjectFactory = $this->getMockBuilder(
            PaymentDataObjectFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $paymentInfo = $this->getMock(InfoInterface::class);
        $paymentDO = $this->getMock(PaymentDataObjectInterface::class);

        $adapter = new Adapter(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            'CODE',
            '\FormBlock',
            '\InfoBlock',
            null,
            null,
            $commandManager
        );

        $valueHandler = $this->getMock(ValueHandlerInterface::class);

        $valueHandlerPool->expects(static::once())
            ->method('get')
            ->with('can_authorize')
            ->willReturn($valueHandler);
        $valueHandler->expects(static::once())
            ->method('handle')
            ->with(['field' => 'can_authorize'])
            ->willReturn(true);

        $paymentDataObjectFactory->expects(static::once())
            ->method('create')
            ->with($paymentInfo)
            ->willReturn($paymentDO);

        $commandManager->expects(static::once())
            ->method('executeByCode')
            ->with('authorize', $paymentInfo, ['amount' => 10, 'payment' => $paymentDO])
            ->willReturn(null);

        $adapter->authorize($paymentInfo, 10);
    }

    public function testExecuteCommandWithCommandPool()
    {
        /** @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->getMock(
            ManagerInterface::class
        );

        /** @var ValueHandlerPoolInterface|\PHPUnit_Framework_MockObject_MockObject $valueHandlerPool */
        $valueHandlerPool = $this->getMock(
            ValueHandlerPoolInterface::class
        );

        /** @var CommandPoolInterface|\PHPUnit_Framework_MockObject_MockObject $commandPool */
        $commandPool = $this->getMock(
            CommandPoolInterface::class
        );

        /** @var PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject $paymentDataObjectFactory */
        $paymentDataObjectFactory = $this->getMockBuilder(
            PaymentDataObjectFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $paymentInfo = $this->getMock(InfoInterface::class);
        $paymentDO = $this->getMock(PaymentDataObjectInterface::class);

        $adapter = new Adapter(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            'CODE',
            '\FormBlock',
            '\InfoBlock',
            $commandPool
        );

        $valueHandler = $this->getMock(ValueHandlerInterface::class);
        $command = $this->getMock(CommandInterface::class);

        $valueHandlerPool->expects(static::once())
            ->method('get')
            ->with('can_authorize')
            ->willReturn($valueHandler);
        $valueHandler->expects(static::once())
            ->method('handle')
            ->with(['field' => 'can_authorize'])
            ->willReturn(true);

        $paymentDataObjectFactory->expects(static::once())
            ->method('create')
            ->with($paymentInfo)
            ->willReturn($paymentDO);

        $commandPool->expects(static::once())
            ->method('get')
            ->with('authorize')
            ->willReturn($command);
        $command->expects(static::once())
            ->method('execute')
            ->with(['amount' => 10, 'payment' => $paymentDO])
            ->willReturn(null);

        $adapter->authorize($paymentInfo, 10);
    }
}
