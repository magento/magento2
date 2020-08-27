<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\ObjectManager\Code\Generator\Converter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var string
     */
    private $sourceClassName;

    /**
     * @var string
     */
    private $resultClassName;

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

    /**
     * @var DefinedClasses|MockObject
     */
    private $definedClassesMock;

    protected function setUp(): void
    {
        $this->sourceClassName = '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample::class;
        $this->resultClassName = '\\' . \Magento\Framework\ObjectManager\Code\Generator\SampleConverter::class;

        $this->ioObjectMock = $this->createMock(Io::class);
        $this->classGenerator = $this->createMock(ClassGenerator::class);

        $this->definedClassesMock = $this->getMockBuilder(DefinedClasses::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->generator = $objectManager->getObject(
            Converter::class,
            [
                'sourceClassName' => $this->sourceClassName,
                'resultClassName' => $this->resultClassName,
                'ioObject' => $this->ioObjectMock,
                'classGenerator' => $this->classGenerator,
                'definedClasses' => $this->definedClassesMock
            ]
        );
    }

    public function testGenerate()
    {
        $generatedCode = 'Generated code';
        $resultFileName = 'SampleConverter.php';

        //Mocking _validateData call
        $this->definedClassesMock->expects($this->at(0))
            ->method('isClassLoadable')
            ->willReturn(true);

        $this->ioObjectMock->expects($this->once())
            ->method('makeResultFileDirectory')
            ->with($this->resultClassName)
            ->willReturn(true);

        //Mocking _generateCode call
        $this->classGenerator->expects($this->once())
            ->method('setName')
            ->with($this->resultClassName)
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
            ->with($this->resultClassName)
            ->willReturn($resultFileName);
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with($resultFileName, $generatedCode);

        $this->assertEquals($resultFileName, $this->generator->generate());
    }
}
