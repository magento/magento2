<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Console\Command\App\SensitiveConfigSet;

use Magento\Deploy\Console\Command\App\SensitiveConfigSet\InteractiveCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\QuestionFactory;

class InteractiveCollectorTest extends TestCase
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
     * @var InteractiveCollector
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
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

        $this->model = new InteractiveCollector(
            $this->questionFactoryMock,
            $this->questionHelperMock
        );
    }

    public function testGetValues()
    {
        $configPaths = [
            'some/config/path1',
            'some/config/path2',
            'some/config/path3'
        ];

        $questionMock = $this->getMockBuilder(Question::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->questionHelperMock->expects($this->exactly(3))
            ->method('ask')
            ->with($this->inputMock, $this->outputMock, $questionMock)
            ->willReturn('someValue');
        $this->questionFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(
                [['question' => $configPaths[0] . ': ']],
                [['question' => $configPaths[1] . ': ']],
                [['question' => $configPaths[2] . ': ']]
            )
            ->willReturn($questionMock);

        $this->assertEquals(
            [
                'some/config/path1' => 'someValue',
                'some/config/path2' => 'someValue',
                'some/config/path3' => 'someValue'
            ],
            $this->model->getValues(
                $this->inputMock,
                $this->outputMock,
                $configPaths
            )
        );
    }
}
