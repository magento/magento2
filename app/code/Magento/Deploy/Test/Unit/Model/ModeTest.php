<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

use Magento\Config\Console\Command\ConfigSet\ProcessorFacadeFactory;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacade;
use Magento\Config\Console\Command\EmulatedAdminhtmlAreaProcessor;
use Magento\Deploy\App\Mode\ConfigProvider;
use Magento\Deploy\Model\Filesystem;
use Magento\Deploy\Model\Mode;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\State;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @inheritdoc
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

    /**
     * @var ConfigProvider|Mock
     */
    private $configProvider;

    /**
     * @var ProcessorFacadeFactory|Mock
     */
    private $processorFacadeFactory;

    /**
     * @var ProcessorFacade|Mock
     */
    private $processorFacade;

    /**
     * @var EmulatedAdminhtmlAreaProcessor|Mock
     */
    private $emulatedAreaProcessor;

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
        $this->configProvider = $this->getMockBuilder(ConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorFacadeFactory = $this->getMockBuilder(ProcessorFacadeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->processorFacade = $this->getMockBuilder(ProcessorFacade::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->emulatedAreaProcessor = $this->getMockBuilder(EmulatedAdminhtmlAreaProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Mode(
            $this->inputMock,
            $this->outputMock,
            $this->writerMock,
            $this->readerMock,
            $this->maintenanceMock,
            $this->filesystemMock,
            $this->configProvider,
            $this->processorFacadeFactory,
            $this->emulatedAreaProcessor
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

    public function testEnableProductionModeMinimal()
    {
        $this->readerMock->expects($this->once())
            ->method('load')
            ->willReturn([State::PARAM_MODE => State::MODE_DEVELOPER]);
        $this->configProvider->expects($this->once())
            ->method('getConfigs')
            ->with('developer', 'production')
            ->willReturn([
                'dev/debug/debug_logging' => 0
            ]);
        $this->emulatedAreaProcessor->expects($this->once())
            ->method('process')
            ->willReturnCallback(function (\Closure $closure) {
                return $closure->call($this->model);
            });

        $this->processorFacadeFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->processorFacade);
        $this->processorFacade
            ->expects($this->once())
            ->method('process')
            ->with(
                'dev/debug/debug_logging',
                0,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                true
            );
        $this->outputMock->expects($this->once())
            ->method('writeln')
            ->with('Config "dev/debug/debug_logging = 0" has been saved.');

        $this->model->enableProductionModeMinimal();
    }
}
