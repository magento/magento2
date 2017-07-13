<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

use Magento\Deploy\Model\Filesystem;
use Magento\Deploy\Model\Mode;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\State;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @inheritdoc
 */
class ModeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Mode
     */
    private $model;

    /**
     * @var Reader|Mock
     */
    private $readerMock;

    /**
     * @var InputInterface|Mock
     */
    private $inputMock;

    /**
     * @var OutputInterface|Mock
     */
    private $outputMock;

    /**
     * @var Writer|Mock
     */
    private $writerMock;

    /**
     * @var MaintenanceMode|Mock
     */
    private $maintenanceMock;

    /**
     * @var Filesystem|Mock
     */
    private $filesystemMock;

    protected function setUp()
    {
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->writerMock = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerMock = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->maintenanceMock = $this->getMockBuilder(MaintenanceMode::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Mode(
            $this->inputMock,
            $this->outputMock,
            $this->writerMock,
            $this->readerMock,
            $this->maintenanceMock,
            $this->filesystemMock
        );
    }

    public function testGetMode()
    {
        $this->readerMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnOnConsecutiveCalls(
                [],
                [State::PARAM_MODE => State::MODE_DEVELOPER]
            );

        $this->assertSame(null, $this->model->getMode());
        $this->assertSame(State::MODE_DEVELOPER, $this->model->getMode());
    }
}
