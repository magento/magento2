<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\MessageGenerators;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Signifyd\Model\MessageGenerators\PatternGenerator;
use Magento\Signifyd\Model\MessageGenerators\CaseRescore;
use Magento\Signifyd\Model\MessageGenerators\GeneratorFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Contains tests for messages generators factory.
 */
class GeneratorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GeneratorFactory
     */
    private $factory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $fakeObjectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->fakeObjectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();

        $this->factory = $objectManager->getObject(GeneratorFactory::class, [
            'objectManager' => $this->fakeObjectManager
        ]);
    }

    /**
     * Checks if factory returns correct instance of message generator.
     *
     * @covers \Magento\Signifyd\Model\MessageGenerators\GeneratorFactory::create
     * @param string $type
     * @param string $className
     * @dataProvider typeDataProvider
     */
    public function testCreate($type, $className)
    {
        $generator = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fakeObjectManager->expects(self::once())
            ->method('create')
            ->with($className)
            ->willReturn($generator);

        $instance = $this->factory->create($type);
        self::assertInstanceOf($className, $instance);
    }

    /**
     * Get list of available messages generators types and equal class names.
     *
     * @return array
     */
    public function typeDataProvider()
    {
        return [
            ['cases/creation', PatternGenerator::class],
            ['cases/review', PatternGenerator::class],
            ['cases/rescore', CaseRescore::class],
            ['guarantees/completion', PatternGenerator::class],
            ['guarantees/creation', PatternGenerator::class],
        ];
    }

    /**
     * Checks correct exception message for unknown type of message generator.
     *
     * @covers \Magento\Signifyd\Model\MessageGenerators\GeneratorFactory::create
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Specified message type does not supported.
     */
    public function testCreateWithException()
    {
        $type = 'cases/unknown';
        $this->factory->create($type);
    }
}
