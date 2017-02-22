<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\Code\Generator;

use Magento\Framework\Code\Generator\Io;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
     * @var \Magento\Framework\Code\Generator\ClassGenerator | \PHPUnit_Framework_MockObject_MockObject
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
        require_once __DIR__ . '/Sample.php';

        $this->ioObjectMock = $this->getMock(
            'Magento\Framework\Code\Generator\Io',
            [],
            [],
            '',
            false
        );
        $this->classGenerator = $this->getMock(
            'Magento\Framework\Code\Generator\ClassGenerator',
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
        $this->definedClassesMock->expects($this->at(0))
            ->method('isClassLoadable')
            ->with($this->getSourceClassName())
            ->willReturn(true);
    }
}
