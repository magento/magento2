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
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\LocalizedException;

/**
 * @inheritdoc
 */
class ModeTest extends \PHPUnit_Framework_TestCase
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

    /**
     * Test that production mode will be enabled before static generation call.
     * We need this to be sure that "min" files will be generated.
     */
    public function testEnableProductionMode()
    {
        $mode = State::MODE_DEVELOPER;
        $modeModel = $this->model;
        $dataStorage = [
            ConfigFilePool::APP_ENV => [
                State::PARAM_MODE => State::MODE_DEVELOPER,
            ],
        ];
        $this->writerMock->expects($this->once())
            ->method("saveConfig")
            ->willReturnCallback(function ($data) use (&$dataStorage) {
                $dataStorage = $data;
            });
        $this->readerMock->expects($this->any())
            ->method('load')
            ->willReturnCallback(function () use (&$dataStorage) {
                return $dataStorage[ConfigFilePool::APP_ENV];
            });
        $this->filesystemMock->expects($this->once())
            ->method("regenerateStatic")
            ->willReturnCallback(function () use (&$modeModel, &$mode) {
                $mode = $modeModel->getMode();
            });
        $this->model->enableProductionMode();
        $this->assertEquals(State::MODE_PRODUCTION, $mode);
    }

    /**
     * Test that previous mode will be enabled after error during static generation call.
     * We need this to be sure that mode will be reverted to it previous tate.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testEnableDeveloperModeOnFail()
    {
        $mode = State::MODE_DEVELOPER;
        $dataStorage = [
            ConfigFilePool::APP_ENV => [
                State::PARAM_MODE => State::MODE_DEVELOPER,
            ],
        ];
        $this->writerMock->expects($this->exactly(2))
            ->method("saveConfig")
            ->withConsecutive(
                [$this->equalTo([ConfigFilePool::APP_ENV => [State::PARAM_MODE => State::MODE_PRODUCTION]])],
                [$this->equalTo([ConfigFilePool::APP_ENV => [State::PARAM_MODE => State::MODE_DEVELOPER]])]
            )
            ->willReturnCallback(function ($data) use (&$dataStorage) {
                $dataStorage = $data;
            });
        $this->readerMock->expects($this->any())
            ->method('load')
            ->willReturnCallback(function () use (&$dataStorage) {
                return $dataStorage[ConfigFilePool::APP_ENV];
            });
        $this->filesystemMock->expects($this->once())
            ->method("regenerateStatic")
            ->willThrowException(new LocalizedException(__('Exception')));
        $this->model->enableProductionMode();
        $this->assertEquals(State::MODE_PRODUCTION, $mode);
    }
}
