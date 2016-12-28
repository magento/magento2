<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console\Test\Unit;

use Magento\Framework\Console\Cli;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CliTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cli
     */
    private $model;

    /**
     * @var InputInterface|Mock
     */
    private $inputMock;

    /**
     * @var OutputInterface|Mock
     */
    private $outputMock;

    protected function setUp()
    {
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Cli();
    }

    public function testDoRun()
    {
        $this->model->doRun(
            $this->inputMock,
            $this->outputMock
        );
    }
}
