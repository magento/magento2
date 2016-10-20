<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Test\Unit;

use Magento\Framework\Code\Generator;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Class name parameter value
     */
    const SOURCE_CLASS = 'testClassName';

    /**
     * Expected generated entities
     *
     * @var array
     */
    protected $expectedEntities = [
        'factory' => \Magento\Framework\ObjectManager\Code\Generator\Factory::ENTITY_TYPE,
        'proxy' => \Magento\Framework\ObjectManager\Code\Generator\Proxy::ENTITY_TYPE,
        'interceptor' => \Magento\Framework\Interception\Code\Generator\Interceptor::ENTITY_TYPE,
    ];

    /**
     * System under test
     *
     * @var \Magento\Framework\Code\Generator
     */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Io */
    protected $ioObjectMock;

    /** @var \Magento\Framework\Code\Generator\DefinedClasses | \PHPUnit_Framework_MockObject_MockObject */
    protected $definedClassesMock;

    protected function setUp()
    {
        $this->definedClassesMock = $this->getMock(\Magento\Framework\Code\Generator\DefinedClasses::class);
        $this->ioObjectMock = $this->getMockBuilder(\Magento\Framework\Code\Generator\Io::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->buildModel(
            $this->ioObjectMock,
            [
                'factory' => \Magento\Framework\ObjectManager\Code\Generator\Factory::class,
                'proxy' => \Magento\Framework\ObjectManager\Code\Generator\Proxy::class,
                'interceptor' => \Magento\Framework\Interception\Code\Generator\Interceptor::class
            ],
            $this->definedClassesMock
        );
    }

    public function testGetGeneratedEntities()
    {
        $this->model = $this->buildModel(
            $this->ioObjectMock,
            ['factory', 'proxy', 'interceptor'],
            $this->definedClassesMock
        );
        $this->assertEquals(array_values($this->expectedEntities), $this->model->getGeneratedEntities());
    }

    /**
     * @expectedException \RuntimeException
     * @dataProvider generateValidClassDataProvider
     */
    public function testGenerateClass($className, $entityType)
    {
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $fullClassName = $className . $entityType;
        $entityGeneratorMock = $this->getMockBuilder(\Magento\Framework\Code\Generator\EntityAbstract::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->once())->method('create')->willReturn($entityGeneratorMock);
        $this->model->setObjectManager($objectManagerMock);
        $this->model->generateClass($fullClassName);
    }

    public function testGenerateClassWithWrongName()
    {
        $this->assertEquals(
            \Magento\Framework\Code\Generator::GENERATION_ERROR,
            $this->model->generateClass(self::SOURCE_CLASS)
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGenerateClassWithError()
    {
        $expectedEntities = array_values($this->expectedEntities);
        $resultClassName = self::SOURCE_CLASS . ucfirst(array_shift($expectedEntities));
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $entityGeneratorMock = $this->getMockBuilder(\Magento\Framework\Code\Generator\EntityAbstract::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->once())->method('create')->willReturn($entityGeneratorMock);
        $this->model->setObjectManager($objectManagerMock);
        $this->model->generateClass($resultClassName);
    }

    /**
     * @dataProvider trueFalseDataProvider
     */
    public function testGenerateClassWithExistName($fileExists)
    {
        $this->definedClassesMock->expects($this->any())
            ->method('isClassLoadableFromDisc')
            ->willReturn(true);

        $resultClassFileName = '/Magento/Path/To/Class.php';
        $this->ioObjectMock->expects($this->once())->method('generateResultFileName')->willReturn($resultClassFileName);
        $this->ioObjectMock->expects($this->once())->method('fileExists')->willReturn($fileExists);
        $includeFileInvokeCount = $fileExists ? 1 : 0;
        $this->ioObjectMock->expects($this->exactly($includeFileInvokeCount))->method('includeFile');

        $this->assertEquals(
            \Magento\Framework\Code\Generator::GENERATION_SKIP,
            $this->model->generateClass(\Magento\GeneratedClass\Factory::class)
        );
    }

    public function trueFalseDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * Data provider for generate class tests
     *
     * @return array
     */
    public function generateValidClassDataProvider()
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

    /**
     * Build SUT object
     *
     * @param Io $ioObject
     * @param array $generatedEntities
     * @param DefinedClasses $definedClasses
     * @return Generator
     */
    private function buildModel(Io $ioObject, array $generatedEntities, DefinedClasses $definedClasses)
    {
        return new Generator($ioObject, $generatedEntities, $definedClasses);
    }
}
