<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Signifyd\Model\CaseUpdatingService;
use Magento\Signifyd\Model\CaseUpdatingServiceFactory;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\MessageGeneratorInterface;
use Magento\Signifyd\Model\MessageGenerators\GeneratorFactory;
use Magento\Signifyd\Model\StubCaseUpdatingService;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Contains tests for case updating service factory.
 */
class CaseUpdatingServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CaseUpdatingServiceFactory
     */
    private $factory;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $fakeObjectManager;

    /**
     * @var GeneratorFactory|MockObject
     */
    private $generatorFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isActive'])
            ->getMock();

        $this->fakeObjectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();

        $this->generatorFactory = $this->getMockBuilder(GeneratorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->factory = $objectManager->getObject(CaseUpdatingServiceFactory::class, [
            'objectManager' => $this->fakeObjectManager,
            'generatorFactory' => $this->generatorFactory,
            'config' => $this->config
        ]);
    }

    /**
     * Checks type of instance for updating service if Signifyd is not enabled.
     *
     * @covers \Magento\Signifyd\Model\CaseUpdatingServiceFactory::create
     */
    public function testCreateWithInactiveConfig()
    {
        $type = 'cases/creation';
        $this->config->expects(self::once())
            ->method('isActive')
            ->willReturn(false);

        $this->fakeObjectManager->expects(self::once())
            ->method('create')
            ->with(StubCaseUpdatingService::class)
            ->willReturn(new StubCaseUpdatingService());

        $instance = $this->factory->create($type);
        static::assertInstanceOf(StubCaseUpdatingService::class, $instance);
    }

    /**
     * Checks type of instance for updating service if test type is received.
     *
     * @covers \Magento\Signifyd\Model\CaseUpdatingServiceFactory::create
     */
    public function testCreateWithTestType()
    {
        $type = 'cases/test';
        $this->config->expects(self::once())
            ->method('isActive')
            ->willReturn(true);

        $this->fakeObjectManager->expects(self::once())
            ->method('create')
            ->with(StubCaseUpdatingService::class)
            ->willReturn(new StubCaseUpdatingService());

        $instance = $this->factory->create($type);
        static::assertInstanceOf(StubCaseUpdatingService::class, $instance);
    }

    /**
     * Checks exception type and message for unknown case type.
     *
     * @covers \Magento\Signifyd\Model\CaseUpdatingServiceFactory::create
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Specified message type does not supported.
     */
    public function testCreateWithException()
    {
        $type = 'cases/unknown';
        $this->config->expects(self::once())
            ->method('isActive')
            ->willReturn(true);

        $this->generatorFactory->expects(self::once())
            ->method('create')
            ->with($type)
            ->willThrowException(new \InvalidArgumentException('Specified message type does not supported.'));

        $this->factory->create($type);
    }

    /**
     * Checks if factory creates correct instance of case updating service.
     *
     * @covers \Magento\Signifyd\Model\CaseUpdatingServiceFactory::create
     */
    public function testCreate()
    {
        $type = 'case/creation';
        $this->config->expects(self::once())
            ->method('isActive')
            ->willReturn(true);

        $messageGenerator = $this->getMockBuilder(MessageGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->generatorFactory->expects(self::once())
            ->method('create')
            ->with($type)
            ->willReturn($messageGenerator);

        $service = $this->getMockBuilder(CaseUpdatingService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fakeObjectManager->expects(self::once())
            ->method('create')
            ->with(
                CaseUpdatingService::class, ['messageGenerator' => $messageGenerator]
            )
            ->willReturn($service);

        $result = $this->factory->create($type);
        static::assertInstanceOf(CaseUpdatingService::class, $result);
    }
}
