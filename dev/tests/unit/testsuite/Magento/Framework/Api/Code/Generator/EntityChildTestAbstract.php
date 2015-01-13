<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\Io;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class BuilderTest
 */
abstract class EntityChildTestAbstract extends \PHPUnit_Framework_TestCase
{
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

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Code\Generator\DefinedClasses */
    protected $definedClassesMock;

    abstract protected function getSourceClassName();

    abstract protected function getResultClassName();

    abstract protected function getGeneratorClassName();

    abstract protected function getOutputFileName();

    protected function setUp()
    {
        require_once __DIR__ . '/_files/Sample.php';

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
            ->method('makeGenerationDirectory')
            ->will($this->returnValue(true));
        $this->ioObjectMock->expects($this->once())
            ->method('makeResultFileDirectory')
            ->with($this->getResultClassName())
            ->will($this->returnValue(true));
        $this->ioObjectMock->expects($this->once())
            ->method('fileExists')
            ->with($resultFileName)
            ->will($this->returnValue(false));

        //Mocking _generateCode call
        $this->classGenerator->expects($this->once())
            ->method('setName')
            ->with($this->getResultClassName())
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
            ->with($this->getResultClassName())
            ->will($this->returnValue($resultFileName));
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
        $this->definedClassesMock->expects($this->at(0))
            ->method('classLoadable')
            ->with($this->getSourceClassName())
            ->willReturn(true);
    }
}
