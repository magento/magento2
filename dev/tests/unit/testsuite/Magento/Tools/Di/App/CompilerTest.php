<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\App;

class CompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Compiler
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Tools\Di\App\Task\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    private $taskManagerMock;

    /**
     * @var \Magento\Framework\App\Console\Response | \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->setMethods([])
            ->getMock();
        $this->taskManagerMock = $this->getMockBuilder('Magento\Tools\Di\App\Task\Manager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Console\Response')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->model = new Compiler(
            $this->taskManagerMock,
            $this->objectManagerMock,
            $this->responseMock
        );
    }

    public function testLaunchSuccess()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('configure')
            ->with($this->getPreferences());
        $index = 0;
        foreach ($this->getOptions() as $code => $arguments) {
            $this->taskManagerMock->expects($this->at($index))
                ->method('addOperation')
                ->with($code, $arguments);
            $index++;
        }
        $this->taskManagerMock->expects($this->at($index))->method('process');
        $this->responseMock->expects($this->once())
            ->method('setCode')
            ->with(\Magento\Framework\App\Console\Response::SUCCESS);

        $this->assertInstanceOf('\Magento\Framework\App\Console\Response', $this->model->launch());
    }

    public function testLaunchException()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('configure')
            ->with($this->getPreferences());
        $code = key($this->getOptions());
        $arguments = current($this->getOptions());
        $exception = new Task\OperationException(
            'Unrecognized operation',
            Task\OperationException::UNAVAILABLE_OPERATION
        );

        $this->taskManagerMock->expects($this->once())
            ->method('addOperation')
            ->with($code, $arguments)
            ->willThrowException($exception);

        $this->taskManagerMock->expects($this->never())->method('process');
        $this->responseMock->expects($this->once())
            ->method('setCode')
            ->with(\Magento\Framework\App\Console\Response::ERROR);

        $this->assertInstanceOf('\Magento\Framework\App\Console\Response', $this->model->launch());
    }

    /**
     * Returns configured preferences
     *
     * @return array
     */
    private function getPreferences()
    {
        return [
            'preferences' =>
                [
                    'Magento\Tools\Di\Compiler\Config\WriterInterface' =>
                        'Magento\Tools\Di\Compiler\Config\Writer\Filesystem'
                ]
        ];
    }

    /**
     * Returns options
     *
     * @return array
     */
    private function getOptions()
    {
        return  [
            Task\OperationFactory::AREA => [
                BP . '/'  . 'app/code', BP . '/'  . 'lib/internal/Magento/Framework', BP . '/'  . 'var/generation'
            ],
            Task\OperationFactory::INTERCEPTION =>
                BP . '/var/generation',
            Task\OperationFactory::INTERCEPTION_CACHE => [
                BP . '/'  . 'app/code', BP . '/'  . 'lib/internal/Magento/Framework', BP . '/'  . 'var/generation'
            ]
        ];
    }
}
