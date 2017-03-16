<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command\App\SensitiveConfigSet;

use Magento\Deploy\Console\Command\App\SensitiveConfigSet\SimpleCollector;
use Magento\Deploy\Console\Command\App\SensitiveConfigSetCommand;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\QuestionFactory;
use Symfony\Component\Console\Helper\QuestionHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class SimpleCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuestionFactory|MockObject
     */
    private $questionFactoryMock;

    /**
     * @var QuestionHelper|MockObject
     */
    private $questionHelperMock;

    /**
     * @var InputInterface|MockObject
     */
    private $inputMock;

    /**
     * @var OutputInterface|MockObject
     */
    private $outputMock;

    /**
     * @var SimpleCollector
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->questionFactoryMock = $this->getMockBuilder(QuestionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->questionHelperMock = $this->getMockBuilder(QuestionHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();

        $this->model = new SimpleCollector(
            $this->questionFactoryMock,
            $this->questionHelperMock
        );
    }

    public function testGetValues()
    {
        $configPaths = [
            'some/config/path1',
            'some/config/path2'
        ];

        $pathQuestionMock = $this->getMockBuilder(Question::class)
            ->disableOriginalConstructor()
            ->getMock();
        $valueQuestionMock = $this->getMockBuilder(Question::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputMock->expects($this->exactly(2))
            ->method('getArgument')
            ->withConsecutive(
                [SensitiveConfigSetCommand::INPUT_ARGUMENT_PATH],
                [SensitiveConfigSetCommand::INPUT_ARGUMENT_VALUE]
            )
            ->willReturnOnConsecutiveCalls(
                $configPaths[0],
                'someValue'
            );
        $this->questionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [['question' => 'Please enter config path: ']],
                [['question' => 'Please enter value: ']]
            )
            ->willReturnOnConsecutiveCalls(
                $pathQuestionMock,
                $valueQuestionMock
            );

        $this->assertEquals(
            ['some/config/path1' => 'someValue'],
            $this->model->getValues(
                $this->inputMock,
                $this->outputMock,
                $configPaths
            )
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage A configuration with this path does not exist or is not sensitive
     */
    public function testWrongConfigPath()
    {
        $configPaths = [
            'some/config/path1',
            'some/config/path2'
        ];

        $pathQuestionMock = $this->getMockBuilder(Question::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputMock->expects($this->once())
            ->method('getArgument')
            ->with(SensitiveConfigSetCommand::INPUT_ARGUMENT_PATH)
            ->willReturn('some/not_exist/config');
        $this->questionFactoryMock->expects($this->once())
            ->method('create')
            ->with(['question' => 'Please enter config path: '])
            ->willReturn($pathQuestionMock);

        $this->model->getValues(
            $this->inputMock,
            $this->outputMock,
            $configPaths
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testEmptyValue()
    {
        $configPaths = [
            'some/config/path1',
            'some/config/path2'
        ];
        $message = 'exception message';

        $pathQuestionMock = $this->getMockBuilder(Question::class)
            ->disableOriginalConstructor()
            ->getMock();
        $valueQuestionMock = $this->getMockBuilder(Question::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->questionHelperMock->expects($this->once())
            ->method('ask')
            ->with($this->inputMock, $this->outputMock, $valueQuestionMock)
            ->willThrowException(new LocalizedException(__($message)));
        $this->inputMock->expects($this->exactly(2))
            ->method('getArgument')
            ->withConsecutive(
                [SensitiveConfigSetCommand::INPUT_ARGUMENT_PATH],
                [SensitiveConfigSetCommand::INPUT_ARGUMENT_VALUE]
            )
            ->willReturnOnConsecutiveCalls(
                $configPaths[0],
                null
            );
        $this->questionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [['question' => 'Please enter config path: ']],
                [['question' => 'Please enter value: ']]
            )
            ->willReturnOnConsecutiveCalls(
                $pathQuestionMock,
                $valueQuestionMock
            );

        $this->model->getValues(
            $this->inputMock,
            $this->outputMock,
            $configPaths
        );
    }
}
