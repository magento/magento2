<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command\App;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\EmulatedAdminhtmlAreaProcessor;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\SensitiveConfigSetFacade;
use Magento\Deploy\Console\Command\App\SensitiveConfigSetCommand;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Framework\Console\Cli;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SensitiveConfigSetCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SensitiveConfigSetFacade|MockObject
     */
    private $facadeMock;

    /**
     * @var ChangeDetector|MockObject
     */
    private $changeDetectorMock;

    /**
     * @var Hash|MockObject
     */
    private $hashMock;

    /**
     * @var EmulatedAdminhtmlAreaProcessor|MockObject
     */
    private $emulatedAreaProcessorMock;

    /**
     * @var SensitiveConfigSetCommand
     */
    private $model;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->facadeMock = $this->getMockBuilder(SensitiveConfigSetFacade::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->changeDetectorMock = $this->getMockBuilder(ChangeDetector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hashMock = $this->getMockBuilder(Hash::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->emulatedAreaProcessorMock = $this->getMockBuilder(EmulatedAdminhtmlAreaProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SensitiveConfigSetCommand(
            $this->facadeMock,
            $this->changeDetectorMock,
            $this->hashMock,
            $this->emulatedAreaProcessorMock
        );
    }

    public function testExecute()
    {
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(false);
        $this->emulatedAreaProcessorMock->expects($this->once())
            ->method('process');
        $this->hashMock->expects($this->once())
            ->method('regenerate')
            ->with(System::CONFIG_TYPE);

        $tester = new CommandTester($this->model);
        $tester->execute([]);

        $this->assertEquals(
            Cli::RETURN_SUCCESS,
            $tester->getStatusCode()
        );
    }

    public function testExecuteNeedsRegeneration()
    {
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(true);
        $this->emulatedAreaProcessorMock->expects($this->never())
            ->method('process');
        $this->hashMock->expects($this->never())
            ->method('regenerate');

        $tester = new CommandTester($this->model);
        $tester->execute([]);

        $this->assertEquals(
            Cli::RETURN_FAILURE,
            $tester->getStatusCode()
        );
        $this->assertContains(
            'This command is unavailable right now.',
            $tester->getDisplay()
        );
    }

    public function testExecuteWithException()
    {
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(false);
        $this->emulatedAreaProcessorMock->expects($this->once())
            ->method('process')
            ->willThrowException(new \Exception('Some exception'));

        $tester = new CommandTester($this->model);
        $tester->execute([]);

        $this->assertEquals(
            Cli::RETURN_FAILURE,
            $tester->getStatusCode()
        );
        $this->assertContains(
            'Some exception',
            $tester->getDisplay()
        );
    }
}
