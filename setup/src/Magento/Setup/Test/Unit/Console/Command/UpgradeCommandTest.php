<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Console\Command\UpgradeCommand;
use Magento\Setup\Model\DbInitStatementsCleanup;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\SearchConfig;
use Magento\Setup\Model\SearchConfigFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UpgradeCommandTest extends TestCase
{
    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var InstallerFactory|MockObject
     */
    private $installerFactoryMock;

    /**
     * @var Installer|MockObject
     */
    private $installerMock;

    /**
     * @var AppState|MockObject
     */
    private $appStateMock;

    /**
     * @var SearchConfig|MockObject
     */
    private $searchConfigMock;

    /**
     * @var DbInitStatementsCleanup|MockObject
     */
    private $dbInitStatementsCleanupMock;

    /**
     * @var UpgradeCommand
     */
    private $upgradeCommand;
    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $objects = [
            [
                CacheInterface::class,
                $this->createMock(CacheInterface::class)
            ]
        ];
        $objectManagerHelper->prepareObjectManager($objects);
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->installerFactoryMock = $this->getMockBuilder(InstallerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->installerMock = $this->getMockBuilder(Installer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->installerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->installerMock);
        $this->appStateMock = $this->getMockBuilder(AppState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchConfigMock = $this->getMockBuilder(SearchConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|SearchConfigFactory $searchConfigFactoryMock */
        $searchConfigFactoryMock = $this->getMockBuilder(SearchConfigFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchConfigFactoryMock->expects($this->once())->method('create')->willReturn($this->searchConfigMock);

        $this->dbInitStatementsCleanupMock = $this->getMockBuilder(DbInitStatementsCleanup::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->upgradeCommand = new UpgradeCommand(
            $this->installerFactoryMock,
            $searchConfigFactoryMock,
            $this->deploymentConfigMock,
            $this->appStateMock,
            null,
            $this->dbInitStatementsCleanupMock
        );
        $this->commandTester = new CommandTester($this->upgradeCommand);
    }

    /**
     * @param array $options
     * @param string $deployMode
     * @param string $expectedString
     * @param array $expectedOptions
     *
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute($options, $deployMode, $expectedString, $expectedOptions): void
    {
        $this->appStateMock->method('getMode')->willReturn($deployMode);
        $this->dbInitStatementsCleanupMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);
        $this->installerMock->expects($this->once())
            ->method('installSchema')
            ->with($expectedOptions);
        $this->installerMock
            ->method('updateModulesSequence');
        $this->installerMock
        ->method('installDataFixtures');

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute($options));
        $this->assertEquals($expectedString, $this->commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public static function executeDataProvider(): array
    {
        $cleanupMessage = "Cleaning up deprecated SET NAMES utf8 from database connections...\n";
        $mediaGalleryNotice = "Media files stored outside of 'Media Gallery Allowed' folders will not be available "
        . "to the media gallery.\n"
        . "Please refer to Developer Guide for more details.\n"
        . "Upgrade completed successfully.\n";

        return [
            [
                'options' => [
                    '--magento-init-params' => '',
                    '--convert-old-scripts' => false
                ],
                'deployMode' => AppState::MODE_PRODUCTION,
                'expectedString' => $cleanupMessage
                    . 'Please re-run Magento compile command. Use the command "setup:di:compile"'
                    . PHP_EOL . $mediaGalleryNotice,
                'expectedOptions' => [
                    'keep-generated' => false,
                    'convert-old-scripts' => false,
                    'safe-mode' => false,
                    'data-restore' => false,
                    'dry-run' => false,
                    'magento-init-params' => ''
                ]
            ],
            [
                'options' => [
                    '--magento-init-params' => '',
                    '--convert-old-scripts' => false,
                    '--keep-generated' => true
                ],
                'deployMode' => AppState::MODE_PRODUCTION,
                'expectedString' => $cleanupMessage . $mediaGalleryNotice,
                'expectedOptions' => [
                    'keep-generated' => true,
                    'convert-old-scripts' => false,
                    'safe-mode' => false,
                    'data-restore' => false,
                    'dry-run' => false,
                    'magento-init-params' => ''
                ]
            ],
            [
                'options' => ['--magento-init-params' => '', '--convert-old-scripts' => false],
                'deployMode' => AppState::MODE_DEVELOPER,
                'expectedString' => $cleanupMessage . $mediaGalleryNotice,
                'expectedOptions' => [
                    'keep-generated' => false,
                    'convert-old-scripts' => false,
                    'safe-mode' => false,
                    'data-restore' => false,
                    'dry-run' => false,
                    'magento-init-params' => ''
                ]
            ],
            [
                'options' => ['--magento-init-params' => '', '--convert-old-scripts' => false],
                'deployMode' => AppState::MODE_DEFAULT,
                'expectedString' => $cleanupMessage . $mediaGalleryNotice,
                'expectedOptions' => [
                    'keep-generated' => false,
                    'convert-old-scripts' => false,
                    'safe-mode' => false,
                    'data-restore' => false,
                    'dry-run' => false,
                    'magento-init-params' => ''
                ]
            ]
        ];
    }
}
