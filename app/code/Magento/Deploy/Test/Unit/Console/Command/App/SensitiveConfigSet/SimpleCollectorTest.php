<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Console\Command\App\SensitiveConfigSet;

use Magento\Deploy\Console\Command\App\SensitiveConfigSet\SimpleCollector;
use Magento\Deploy\Console\Command\App\SensitiveConfigSetCommand;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\QuestionFactory;

class SimpleCollectorTest extends TestCase
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
    protected function setUp(): void
    {
        $this->questionFactoryMock = $this->getMockBuilder(QuestionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
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
            ->willReturnCallback(
                function ($arg) use ($configPaths) {
                    if ($arg === SensitiveConfigSetCommand::INPUT_ARGUMENT_PATH) {
                        return $configPaths[0];
                    } elseif ($arg === SensitiveConfigSetCommand::INPUT_ARGUMENT_VALUE) {
                        return 'someValue';
                    }
                }
            );
        $this->questionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnCallback(
                function ($arg) use ($pathQuestionMock, $valueQuestionMock) {
                    if ($arg['question'] === 'Please enter config path: ') {
                        return $pathQuestionMock;
                    } elseif ($arg['question'] === 'Please enter value: ') {
                        return $valueQuestionMock;
                    }
                }
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

    public function testWrongConfigPath()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('A configuration with this path does not exist or is not sensitive');
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

    public function testEmptyValue()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
            ->willReturnCallback(
                function ($arg) use ($configPaths) {
                    if ($arg === SensitiveConfigSetCommand::INPUT_ARGUMENT_PATH) {
                        return $configPaths[0];
                    } elseif ($arg === SensitiveConfigSetCommand::INPUT_ARGUMENT_VALUE) {
                        return null;
                    }
                }
            );
        $this->questionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnCallback(
                function ($arg) use ($pathQuestionMock, $valueQuestionMock) {
                    if ($arg['question'] === 'Please enter config path: ') {
                        return $pathQuestionMock;
                    } elseif ($arg['question'] === 'Please enter value: ') {
                        return $valueQuestionMock;
                    }
                }
            );

        $this->model->getValues(
            $this->inputMock,
            $this->outputMock,
            $configPaths
        );
    }
}
