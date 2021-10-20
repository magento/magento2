<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\Code\Generator;

use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class BuilderTest
 */
abstract class EntityChildTestAbstract extends TestCase
{
    /**
     * @var Io|MockObject
     */
    protected $ioObjectMock;

    /**
     * @var EntityAbstract
     */
    protected $generator;

    /**
     * @var ClassGenerator|MockObject
     */
    protected $classGenerator;

    /** @var MockObject|DefinedClasses */
    protected $definedClassesMock;

    /**
     * @return mixed
     */
    abstract protected function getSourceClassName();

    /**
     * @return mixed
     */
    abstract protected function getResultClassName();

    /**
     * @return mixed
     */
    abstract protected function getGeneratorClassName();

    /**
     * @return mixed
     */
    abstract protected function getOutputFileName();

    protected function setUp(): void
    {
        require_once __DIR__ . '/Sample.php';

        $this->ioObjectMock = $this->createMock(Io::class);
        $this->classGenerator = $this->createMock(ClassGenerator::class);
        $this->definedClassesMock = $this->getMockBuilder(DefinedClasses::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->generator = $objectManager->getObject(
            $this->getGeneratorClassName(),
            [
                'sourceClassName' => $this->getSourceClassName(),
                'resultClassName' => $this->getResultClassName(),
                'ioObject' => $this->ioObjectMock,
                'classGenerator' => $this->classGenerator,
                'definedClasses' => $this->definedClassesMock,
            ]
        );
    }

    /**
     * generate repository name
     */
    public function testGenerate()
    {
        $generatedCode = 'Generated code';
        $resultFileName = $this->getOutputFileName();

        //Mocking _validateData call
        $this->mockDefinedClassesCall();

        $this->ioObjectMock->expects($this->once())
            ->method('makeResultFileDirectory')
            ->with($this->getResultClassName())
            ->willReturn(true);

        //Mocking _generateCode call
        $this->classGenerator->expects($this->once())
            ->method('setName')
            ->with($this->getResultClassName())
            ->willReturnSelf();
        $this->classGenerator->expects($this->once())
            ->method('addProperties')
            ->willReturnSelf();
        $this->classGenerator->expects($this->once())
            ->method('addMethods')
            ->willReturnSelf();
        $this->classGenerator->expects($this->once())
            ->method('setClassDocBlock')
            ->willReturnSelf();
        $this->classGenerator->expects($this->once())
            ->method('generate')
            ->willReturn($generatedCode);

        //Mocking generation
        $this->ioObjectMock->expects($this->any())
            ->method('generateResultFileName')
            ->with($this->getResultClassName())
            ->willReturn($resultFileName);
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with($resultFileName, $generatedCode);

        $this->assertEquals(
            $resultFileName,
            $this->generator->generate(),
            implode("\n", $this->generator->getErrors())
        );
    }

    protected function mockDefinedClassesCall()
    {
        $this->definedClassesMock
            ->method('isClassLoadable')
            ->with($this->getSourceClassName())
            ->willReturn(true);
    }
}
