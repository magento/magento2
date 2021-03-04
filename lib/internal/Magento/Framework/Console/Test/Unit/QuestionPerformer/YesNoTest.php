<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console\Test\Unit\QuestionPerformer;

use Magento\Framework\Console\QuestionPerformer\YesNo;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\QuestionFactory;
use Symfony\Component\Console\Question\Question;

class YesNoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InputInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $inputMock;

    /**
     * @var OutputInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $outputMock;

    /**
     * @var QuestionHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $questionHelperMock;

    /**
     * @var QuestionFactory|\PHPUnit\Framework\MockObject\MockObject
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

        /** @var Question|\PHPUnit\Framework\MockObject\MockObject $questionMock */
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
