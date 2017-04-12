<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

use Magento\Framework\Code\Generator\Io;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ConverterTest
 * @package Magento\Framework\ObjectManager\Code\Generator
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
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
     * @var Io | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ioObjectMock;

    /**
     * @var \Magento\Framework\Code\Generator\EntityAbstract
     */
    protected $generator;

    /**
     * @var \Magento\Framework\Code\Generator\ClassGenerator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $classGenerator;

    /**
     * @var \Magento\Framework\Code\Generator\DefinedClasses | \PHPUnit_Framework_MockObject_MockObject
     */
    private $definedClassesMock;

    protected function setUp()
    {
        $this->sourceClassName = '\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample::class;
        $this->resultClassName = '\\' . \Magento\Framework\ObjectManager\Code\Generator\SampleConverter::class;

        $this->ioObjectMock = $this->getMock(
            \Magento\Framework\Code\Generator\Io::class,
            [],
            [],
            '',
            false
        );
        $this->classGenerator = $this->getMock(
            \Magento\Framework\Code\Generator\ClassGenerator::class,
            [],
            [],
            '',
            false
        );

        $this->definedClassesMock = $this->getMockBuilder(\Magento\Framework\Code\Generator\DefinedClasses::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new ObjectManager($this);
        $this->generator = $objectManager->getObject(
            \Magento\Framework\ObjectManager\Code\Generator\Converter::class,
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
            ->will($this->returnValue(true));

        $this->ioObjectMock->expects($this->once())
            ->method('makeResultFileDirectory')
            ->with($this->resultClassName)
            ->will($this->returnValue(true));

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
            ->will($this->returnValue($generatedCode));

        //Mocking generation
        $this->ioObjectMock->expects($this->any())
            ->method('generateResultFileName')
            ->with($this->resultClassName)
            ->will($this->returnValue($resultFileName));
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with($resultFileName, $generatedCode);

        $this->assertEquals($resultFileName, $this->generator->generate());
    }
}
