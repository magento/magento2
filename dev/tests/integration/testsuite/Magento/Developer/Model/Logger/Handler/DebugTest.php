<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Logger\Handler;

use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Logger\Monolog;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Deploy\Model\Mode;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Monolog
     */
    private $logger;

    /**
     * @var Mode
     */
    private $mode;

    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputMock;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $outputMock;

    /**
     * @var ConfigSetCommand
     */
    private $configSetCommand;

    /**
     * @var WriteInterface
     */
    private $etcDirectory;

    public function setUp()
    {
        /** @var Filesystem $filesystem */
        $filesystem = Bootstrap::getObjectManager()->create(Filesystem::class);
        $this->etcDirectory = $filesystem->getDirectoryWrite(DirectoryList::CONFIG);
        $this->etcDirectory->copyFile('env.php', 'env.base.php');

        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->logger = Bootstrap::getObjectManager()->get(Monolog::class);
        $this->mode = Bootstrap::getObjectManager()->create(
            Mode::class,
            [
                'input' => $this->inputMock,
                'output' => $this->outputMock
            ]
        );
        $this->configSetCommand = Bootstrap::getObjectManager()->create(ConfigSetCommand::class);

        // Preconditions
        $this->mode->enableDeveloperMode();
        if (file_exists($this->getDebuggerLogPath())) {
            unlink($this->getDebuggerLogPath());
        }
    }

    public function tearDown()
    {
        $this->etcDirectory->delete('env.php');
        $this->etcDirectory->renameFile('env.base.php', 'env.php');
    }

    public function testDebugInProductionMode()
    {
        $message = 'test message';

        $this->mode->enableProductionModeMinimal();

        $this->logger->debug($message);
        $this->assertFileNotExists($this->getDebuggerLogPath());

        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->inputMock->expects($this->exactly(2))
            ->method('getArgument')
            ->withConsecutive([ConfigSetCommand::ARG_PATH], [ConfigSetCommand::ARG_VALUE])
            ->willReturnOnConsecutiveCalls('dev/debug/debug_logging', 1);
        $this->inputMock->expects($this->exactly(3))
            ->method('getOption')
            ->withConsecutive(
                [ConfigSetCommand::OPTION_SCOPE],
                [ConfigSetCommand::OPTION_SCOPE_CODE],
                [ConfigSetCommand::OPTION_LOCK]
            )
            ->willReturnOnConsecutiveCalls(
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                true
            );
        $this->outputMock->expects($this->once())
            ->method('writeln')
            ->with('<info>Value was saved and locked.</info>');
        $this->assertFalse((bool)$this->configSetCommand->run($this->inputMock, $this->outputMock));
        $this->logger->debug($message);

        $this->assertFileExists($this->getDebuggerLogPath());
        $this->assertContains($message, file_get_contents($this->getDebuggerLogPath()));
    }

    /**
     * @return bool|string
     */
    private function getDebuggerLogPath()
    {
        foreach ($this->logger->getHandlers() as $handler) {
            if ($handler instanceof Debug) {
                return $handler->getUrl();
            }
        }
        return false;
    }
}
