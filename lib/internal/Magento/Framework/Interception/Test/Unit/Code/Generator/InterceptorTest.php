<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Interception\Test\Unit\Code\Generator;

class InterceptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ioObjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $classGeneratorMock;

    protected function setUp()
    {
        $this->ioObjectMock = $this->getMock('\Magento\Framework\Code\Generator\Io', [], [], '', false);
        $this->classGeneratorMock = $this->getMock(
            '\Magento\Framework\Code\Generator\CodeGeneratorInterface',
            [],
            [],
            '',
            false
        );
    }

    public function testGetDefaultResultClassName()
    {
        // resultClassName should be stdClass_Interceptor
        $model = $this->getMock('\Magento\Framework\Interception\Code\Generator\Interceptor',
            ['_validateData'],
            [
                'Exception',
                null,
                $this->ioObjectMock,
                $this->classGeneratorMock,
            ]
        );

        $this->classGeneratorMock->expects($this->once())->method('setName')
            ->willReturnSelf();
        $this->classGeneratorMock->expects($this->once())->method('addProperties')
            ->willReturnSelf();
        $this->classGeneratorMock->expects($this->once())->method('addMethods')
            ->willReturnSelf();
        $this->classGeneratorMock->expects($this->once())->method('setClassDocBlock')
            ->willReturnSelf();
        $this->classGeneratorMock->expects($this->once())->method('generate')
            ->will($this->returnValue('source code example'));
        $model->expects($this->once())->method('_validateData')->will($this->returnValue(true));
        $this->ioObjectMock->expects($this->any())->method('generateResultFileName')->with('Exception_Interceptor');
        $this->assertEquals('', $model->generate());
    }
}
