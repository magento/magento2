<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Console\Test\Unit\QuestionPerformer;

use Magento\Framework\Console\QuestionPerformer\YesNo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\QuestionFactory;

class YesNoTest extends TestCase
{
    /**
     * @var InputInterface|MockObject
     */
    private $inputMock;

    /**
     * @var OutputInterface|MockObject
     */
    private $outputMock;

    /**
     * @var QuestionHelper|MockObject
     */
    private $questionHelperMock;

    /**
     * @var QuestionFactory|MockObject
     */
    private $questionFactoryMock;

    /**
     * @var YesNo
     */
    private $questionPerformer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->questionFactoryMock = $this->getMockBuilder(QuestionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->questionHelperMock = $this->getMockBuilder(QuestionHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->questionPerformer = new YesNo($this->questionHelperMock, $this->questionFactoryMock);
    }

    /**
     * @param string $answer
     * @param bool $expectedResult
     * @dataProvider executeDataProvider
     */
    public function testExecute($answer, $expectedResult)
    {
        $firstMessage = 'First message';
        $secondMessage = 'Second message';
        $messages = [$firstMessage, $secondMessage];

        /** @var Question|MockObject $questionMock */
        $questionMock = $this->getMockBuilder(Question::class)
            ->disableOriginalConstructor()
            ->getMock();
        $questionMock->expects($this->once())
            ->method('setValidator');
        $this->questionFactoryMock->expects($this->once())
            ->method('create')
            ->with(['question' => $firstMessage . PHP_EOL . $secondMessage . PHP_EOL])
            ->willReturn($questionMock);
        $this->questionHelperMock->expects($this->once())
            ->method('ask')
            ->with($this->inputMock, $this->outputMock, $questionMock)
            ->willReturn($answer);
        $this->inputMock->expects($this->once())
            ->method('isInteractive')
            ->willReturn(true);

        $this->assertSame(
            $expectedResult,
            $this->questionPerformer->execute($messages, $this->inputMock, $this->outputMock)
        );
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['yes', true],
            ['Yes', true],
            ['YES', true],
            ['y', true],
            ['Y', true],
            ['ya', false],
            ['no', false],
            ['NO', false],
            ['n', false],
            ['N', false],
            ['Not', false],
            ['anykey', false]
        ];
    }
}
