<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
namespace Magento\Framework\Code\Test\Unit;

use Magento\Framework\Code\Generator;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;
<<<<<<< HEAD
=======
use Magento\Framework\ObjectManager\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Psr\Log\LoggerInterface;
use Magento\Framework\ObjectManager\Code\Generator\Factory;
use Magento\Framework\ObjectManager\Code\Generator\Proxy;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\GeneratedClass\Factory as GeneratedClassFactory;
<<<<<<< HEAD

=======
use RuntimeException;

/**
 * Tests for code generator.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
class GeneratorTest extends TestCase
{
    /**
     * Class name parameter value
     */
    private const SOURCE_CLASS = 'testClassName';

    /**
     * Expected generated entities
     *
     * @var array
     */
    private $expectedEntities = [
        'factory' => Factory::ENTITY_TYPE,
        'proxy' => Proxy::ENTITY_TYPE,
        'interceptor' => Interceptor::ENTITY_TYPE,
    ];

    /**
     * System under test
     *
     * @var Generator
<<<<<<< HEAD
     */
    private $model;

    /**
     * @var Io|Mock
     */
    private $ioObjectMock;

    /**
     * @var DefinedClasses|Mock
     */
    private $definedClassesMock;

    /**
     * @var LoggerInterface|Mock
     */
    private $loggerMock;
=======
     */
    private $model;

    /**
     * @var Io|Mock
     */
    private $ioObjectMock;

    /**
     * @var DefinedClasses|Mock
     */
    private $definedClassesMock;

    /**
     * @var LoggerInterface|Mock
     */
    private $loggerMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $objectManagerConfigMock;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->definedClassesMock = $this->createMock(DefinedClasses::class);
        $this->ioObjectMock = $this->getMockBuilder(Io::class)
<<<<<<< HEAD
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
=======
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        $this->model = new Generator(
            $this->ioObjectMock,
            [
                'factory' => Factory::class,
                'proxy' => Proxy::class,
                'interceptor' => Interceptor::class,
            ],
            $this->definedClassesMock,
            $this->loggerMock
        );
    }

    public function testGetGeneratedEntities(): void
    {
        $this->model = new Generator(
            $this->ioObjectMock,
            ['factory', 'proxy', 'interceptor'],
            $this->definedClassesMock
        );
        $this->assertEquals(array_values($this->expectedEntities), $this->model->getGeneratedEntities());
    }

    /**
     * @param string $className
     * @param string $entityType
<<<<<<< HEAD
     * @expectedException \RuntimeException
=======
     * @expectedException RuntimeException
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @dataProvider generateValidClassDataProvider
     */
    public function testGenerateClass($className, $entityType): void
    {
<<<<<<< HEAD
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $fullClassName = $className . $entityType;
=======
        $fullClassName = $className . $entityType;

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $entityGeneratorMock = $this->getMockBuilder(EntityAbstract::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($entityGeneratorMock);

        $this->objectManagerConfigMock
            ->expects($this->once())
            ->method('getVirtualTypes')
            ->willReturn([]);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->objectManagerConfigMock);
        $this->model->setObjectManager($this->objectManagerMock);

        $this->assertSame(
            Generator::GENERATION_SUCCESS,
            $this->model->generateClass($fullClassName)
        );
    }

    public function testShouldNotGenerateVirtualType(): void
    {
        $this->objectManagerConfigMock
            ->expects($this->once())
            ->method('getVirtualTypes')
            ->willReturn([GeneratedClassFactory::class => GeneratedClassFactory::class]);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->objectManagerConfigMock);
        $this->model->setObjectManager($this->objectManagerMock);

        $this->assertSame(
            Generator::GENERATION_SKIP,
            $this->model->generateClass(GeneratedClassFactory::class)
        );
    }

    public function testGenerateClassWithWrongName(): void
    {
        $this->assertEquals(
            Generator::GENERATION_ERROR,
            $this->model->generateClass(self::SOURCE_CLASS)
        );
    }

    /**
     * @expectedException RuntimeException
     */
<<<<<<< HEAD
    public function testGenerateClassWhenClassIsNotGenerationSuccess()
    {
        $expectedEntities = array_values($this->expectedEntities);
        $resultClassName = self::SOURCE_CLASS . ucfirst(array_shift($expectedEntities));
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
=======
    public function testGenerateClassWhenClassIsNotGenerationSuccess(): void
    {
        $expectedEntities = array_values($this->expectedEntities);
        $resultClassName = self::SOURCE_CLASS . ucfirst(array_shift($expectedEntities));

        $entityGeneratorMock = $this->getMockBuilder(EntityAbstract::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($entityGeneratorMock);

        $this->objectManagerConfigMock
            ->expects($this->once())
            ->method('getVirtualTypes')
            ->willReturn([]);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->objectManagerConfigMock);
        $this->model->setObjectManager($this->objectManagerMock);

        $this->assertSame(
            Generator::GENERATION_SUCCESS,
            $this->model->generateClass($resultClassName)
        );
    }

    /**
     * @inheritdoc
     */
    public function testGenerateClassWithErrors(): void
    {
        $expectedEntities = array_values($this->expectedEntities);
        $resultClassName = self::SOURCE_CLASS . ucfirst(array_shift($expectedEntities));
        $errorMessages = [
            'Error message 0',
            'Error message 1',
            'Error message 2',
        ];
        $mainErrorMessage = 'Class ' . $resultClassName . ' generation error: The requested class did not generate '
            . 'properly, because the \'generated\' directory permission is read-only. '
            . 'If --- after running the \'bin/magento setup:di:compile\' CLI command when the \'generated\' '
            . 'directory permission is set to write --- the requested class did not generate properly, then '
            . 'you must add the generated class object to the signature of the related construct method, only.';
        $FinalErrorMessage = implode(PHP_EOL, $errorMessages) . "\n" . $mainErrorMessage;
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($FinalErrorMessage);

        /** @var EntityAbstract|Mock $entityGeneratorMock */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $entityGeneratorMock = $this->getMockBuilder(EntityAbstract::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($entityGeneratorMock);
        $entityGeneratorMock->expects($this->once())
            ->method('getSourceClassName')
            ->willReturn(self::SOURCE_CLASS);
        $this->definedClassesMock->expects($this->once())
            ->method('isClassLoadable')
            ->with(self::SOURCE_CLASS)
            ->willReturn(true);
        $entityGeneratorMock->expects($this->once())
            ->method('generate')
            ->willReturn(false);
        $entityGeneratorMock->expects($this->once())
            ->method('getErrors')
            ->willReturn($errorMessages);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($FinalErrorMessage);

        $this->objectManagerConfigMock
            ->expects($this->once())
            ->method('getVirtualTypes')
            ->willReturn([]);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->objectManagerConfigMock);
        $this->model->setObjectManager($this->objectManagerMock);

        $this->assertSame(
            Generator::GENERATION_SUCCESS,
            $this->model->generateClass($resultClassName)
        );
    }

    /**
     * @inheritdoc
     */
    public function testGenerateClassWithErrors()
    {
        $expectedEntities = array_values($this->expectedEntities);
        $resultClassName = self::SOURCE_CLASS . ucfirst(array_shift($expectedEntities));
        $errorMessages = [
            'Error message 0',
            'Error message 1',
            'Error message 2',
        ];
        $mainErrorMessage = 'Class ' . $resultClassName . ' generation error: The requested class did not generate '
            . 'properly, because the \'generated\' directory permission is read-only. '
            . 'If --- after running the \'bin/magento setup:di:compile\' CLI command when the \'generated\' '
            . 'directory permission is set to write --- the requested class did not generate properly, then '
            . 'you must add the generated class object to the signature of the related construct method, only.';
        $FinalErrorMessage = implode(PHP_EOL, $errorMessages) . "\n" . $mainErrorMessage;
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($FinalErrorMessage);

        /** @var ObjectManagerInterface|Mock $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        /** @var EntityAbstract|Mock $entityGeneratorMock */
        $entityGeneratorMock = $this->getMockBuilder(EntityAbstract::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($entityGeneratorMock);
        $entityGeneratorMock->expects($this->once())
            ->method('getSourceClassName')
            ->willReturn(self::SOURCE_CLASS);
        $this->definedClassesMock->expects($this->once())
            ->method('isClassLoadable')
            ->with(self::SOURCE_CLASS)
            ->willReturn(true);
        $entityGeneratorMock->expects($this->once())
            ->method('generate')
            ->willReturn(false);
        $entityGeneratorMock->expects($this->once())
            ->method('getErrors')
            ->willReturn($errorMessages);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($FinalErrorMessage);
        $this->model->setObjectManager($objectManagerMock);
        $this->model->generateClass($resultClassName);
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param $fileExists
     */
    public function testGenerateClassWithExistName($fileExists): void
    {
        $this->definedClassesMock->expects($this->any())
            ->method('isClassLoadableFromDisk')
            ->willReturn(true);

        $resultClassFileName = '/Magento/Path/To/Class.php';

        $this->objectManagerConfigMock
            ->expects($this->once())
            ->method('getVirtualTypes')
            ->willReturn([]);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->objectManagerConfigMock);
        $this->model->setObjectManager($this->objectManagerMock);

        $this->ioObjectMock
            ->expects($this->once())
            ->method('generateResultFileName')
            ->willReturn($resultClassFileName);
        $this->ioObjectMock
            ->expects($this->once())
            ->method('fileExists')
            ->willReturn($fileExists);

        $includeFileInvokeCount = $fileExists ? 1 : 0;
        $this->ioObjectMock
            ->expects($this->exactly($includeFileInvokeCount))
            ->method('includeFile');

<<<<<<< HEAD
        $this->assertEquals(
=======
        $this->assertSame(
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            Generator::GENERATION_SKIP,
            $this->model->generateClass(GeneratedClassFactory::class)
        );
    }

    /**
     * @return array
     */
    public function trueFalseDataProvider(): array
    {
        return [[true], [false]];
    }

    /**
     * Data provider for generate class tests
     *
     * @return array
     */
    public function generateValidClassDataProvider(): array
    {
        $data = [];
        foreach ($this->expectedEntities as $generatedEntity) {
            $generatedEntity = ucfirst($generatedEntity);
            $data['test class for ' . $generatedEntity] = [
                'class name' => self::SOURCE_CLASS,
                'entity type' => $generatedEntity,
            ];
        }
        return $data;
    }
}
