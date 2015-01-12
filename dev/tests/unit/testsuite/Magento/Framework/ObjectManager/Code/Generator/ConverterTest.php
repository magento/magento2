<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Code\Generator;

use Magento\Framework\Code\Generator\Io;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class ConverterTest
 * @package Magento\Framework\ObjectManager\Code\Generator
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    const SOURCE_CLASS_NAME = 'Magento\Framework\ObjectManager\Code\Generator\Sample';
    const RESULT_CLASS_NAME = 'Magento\Framework\ObjectManager\Code\Generator\SampleConverter';

    /**
     * @var Io | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ioObjectMock;

    /**
     * @var \Magento\Framework\Code\Generator\EntityAbstract
     */
    protected $generator;

    /**
     * @var \Magento\Framework\Code\Generator\CodeGenerator\Zend | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $classGenerator;

    /**
     * @var \Magento\Framework\Code\Generator\DefinedClasses | \PHPUnit_Framework_MockObject_MockObject
     */
    private $definedClassesMock;

    protected function setUp()
    {
        $this->ioObjectMock = $this->getMock(
            'Magento\Framework\Code\Generator\Io',
            [],
            [],
            '',
            false
        );
        $this->classGenerator = $this->getMock(
            'Magento\Framework\Code\Generator\CodeGenerator\Zend',
            [],
            [],
            '',
            false
        );

        $this->definedClassesMock = $this->getMockBuilder('Magento\Framework\Code\Generator\DefinedClasses')
            ->disableOriginalConstructor()->getMock();

        $objectManager = new ObjectManager($this);
        $this->generator = $objectManager->getObject(
            'Magento\Framework\ObjectManager\Code\Generator\Converter',
            [
                'sourceClassName' => self::SOURCE_CLASS_NAME,
                'resultClassName' => self::RESULT_CLASS_NAME,
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
            ->method('classLoadable')
            ->will($this->returnValue(true));

        $this->ioObjectMock->expects($this->once())
            ->method('makeGenerationDirectory')
            ->will($this->returnValue(true));
        $this->ioObjectMock->expects($this->once())
            ->method('makeResultFileDirectory')
            ->with(self::RESULT_CLASS_NAME)
            ->will($this->returnValue(true));
        $this->ioObjectMock->expects($this->once())
            ->method('fileExists')
            ->with($resultFileName)
            ->will($this->returnValue(false));

        //Mocking _generateCode call
        $this->classGenerator->expects($this->once())
            ->method('setName')
            ->with(self::RESULT_CLASS_NAME)
            ->will($this->returnSelf());
        $this->classGenerator->expects($this->once())
            ->method('addProperties')
            ->will($this->returnSelf());
        $this->classGenerator->expects($this->once())
            ->method('addMethods')
            ->will($this->returnSelf());
        $this->classGenerator->expects($this->once())
            ->method('setClassDocBlock')
            ->will($this->returnSelf());
        $this->classGenerator->expects($this->once())
            ->method('generate')
            ->will($this->returnValue($generatedCode));

        //Mocking generation
        $this->ioObjectMock->expects($this->any())
            ->method('getResultFileName')
            ->with(self::RESULT_CLASS_NAME)
            ->will($this->returnValue($resultFileName));
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with($resultFileName, $generatedCode);

        $this->assertEquals($resultFileName, $this->generator->generate());
    }
}
