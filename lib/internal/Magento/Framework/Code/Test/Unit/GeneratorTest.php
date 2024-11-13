<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\Test\Unit;

use Magento\Framework\Code\Generator;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\ObjectManager\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Magento\Framework\ObjectManager\Code\Generator\Factory;
use Magento\Framework\ObjectManager\Code\Generator\Proxy;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Code\Generator\EntityAbstract;
use RuntimeException;

/**
 * Tests for code generator.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneratorTest extends TestCase
{
    /** parameter value
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->definedClassesMock = $this->createMock(DefinedClasses::class);
        $this->ioObjectMock = $this->getMockBuilder(Io::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
     * @dataProvider generateValidClassDataProvider
     */
    public function testGenerateClass($className, $entityType): void
    {
        $this->expectException('RuntimeException');
        $fullClassName = $className . $entityType;

        $entityGeneratorMock = $this->getMockForAbstractClass(
            EntityAbstract::class,
            [],
            '',
            true,
            true,
            true,
            ['getSourceClassName']
        );
        $entityGeneratorMock->method('getSourceClassName')->willReturn('');
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
            ->willReturn(['Magento\GeneratedClass\Factory' => 'Magento\GeneratedClass\Factory']);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->objectManagerConfigMock);
        $this->model->setObjectManager($this->objectManagerMock);

        $this->assertSame(
            Generator::GENERATION_SKIP,
            $this->model->generateClass('Magento\GeneratedClass\Factory')
        );
    }

    public function testGenerateClassWithWrongName(): void
    {
        $this->assertEquals(
            Generator::GENERATION_ERROR,
            $this->model->generateClass(self::SOURCE_CLASS)
        );
    }

    public function testGenerateClassWhenClassIsNotGenerationSuccess(): void
    {
        $this->expectException('RuntimeException');
        $expectedEntities = array_values($this->expectedEntities);
        $resultClassName = self::SOURCE_CLASS . ucfirst(array_shift($expectedEntities));

        $entityGeneratorMock = $this->getMockForAbstractClass(
            EntityAbstract::class,
            [],
            '',
            true,
            true,
            true,
            ['getSourceClassName']
        );
        $entityGeneratorMock->method('getSourceClassName')->willReturn('');
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

        $this->assertSame(
            Generator::GENERATION_SKIP,
            $this->model->generateClass('Magento\GeneratedClass\Factory')
        );
    }

    /**
     * @return array
     */
    public static function trueFalseDataProvider(): array
    {
        return [[true], [false]];
    }

    /**
     * Data provider for generate class tests
     *
     * @return array
     */
    public static function generateValidClassDataProvider(): array
    {
        $expectedEntities = [
            'factory' => Factory::ENTITY_TYPE,
            'proxy' => Proxy::ENTITY_TYPE,
            'interceptor' => Interceptor::ENTITY_TYPE,
        ];
        $data = [];
        foreach ($expectedEntities as $generatedEntity) {
            $generatedEntity = ucfirst($generatedEntity);
            $data['test class for ' . $generatedEntity] = [
                'className' => self::SOURCE_CLASS,
                'entityType' => $generatedEntity,
            ];
        }
        return $data;
    }
}
