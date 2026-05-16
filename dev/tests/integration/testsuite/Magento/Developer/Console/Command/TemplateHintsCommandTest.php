<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Magento\Developer\Test\Fixture\LockedTemplateHintsConfig;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\DataFixture;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Integration tests for template hints CLI commands with locked config detection.
 *
 * @see https://github.com/magento/magento2/issues/40523
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class TemplateHintsCommandTest extends TestCase
{
    private const CONFIG_PATH = 'dev/debug/template_hints_storefront';

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var Writer
     */
    private Writer $writer;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var ConfigFilePool
     */
    private ConfigFilePool $configFilePool;

    /**
     * @var ReinitableConfigInterface
     */
    private ReinitableConfigInterface $appConfig;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var array
     */
    private array $envConfig;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $reader = $this->objectManager->get(FileReader::class);
        $this->writer = $this->objectManager->get(Writer::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->appConfig = $this->objectManager->get(ReinitableConfigInterface::class);
        $this->resourceConnection = $this->objectManager->get(ResourceConnection::class);

        $this->envConfig = $reader->load(ConfigFilePool::APP_ENV);
        $this->deleteConfigValue();
        $this->appConfig->reinit();
    }

    protected function tearDown(): void
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => $this->envConfig]);
        $this->appConfig->reinit();
    }

    public function testEnableSucceedsWhenConfigNotLocked(): void
    {
        $command = $this->objectManager->create(TemplateHintsEnableCommand::class);
        $tester = new CommandTester($command);

        $tester->execute([]);

        $this->assertSame(Cli::RETURN_SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('Template hints enabled', $tester->getDisplay());
        $this->assertSame('1', $this->getConfigValue());
    }

    #[DataFixture(LockedTemplateHintsConfig::class, ['value' => '0'])]
    public function testEnableFailsWhenConfigLocked(): void
    {
        $command = $this->objectManager->create(TemplateHintsEnableCommand::class);
        $tester = new CommandTester($command);

        $tester->execute([]);

        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('locked', $tester->getDisplay());
        $this->assertNull($this->getConfigValue());
    }

    public function testDisableSucceedsWhenConfigNotLocked(): void
    {
        $command = $this->objectManager->create(TemplateHintsDisableCommand::class);
        $tester = new CommandTester($command);

        $tester->execute([]);

        $this->assertSame(Cli::RETURN_SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('Template hints disabled', $tester->getDisplay());
        $this->assertSame('0', $this->getConfigValue());
    }

    #[DataFixture(LockedTemplateHintsConfig::class, ['value' => '1'])]
    public function testDisableFailsWhenConfigLocked(): void
    {
        $command = $this->objectManager->create(TemplateHintsDisableCommand::class);
        $tester = new CommandTester($command);

        $tester->execute([]);

        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('locked', $tester->getDisplay());
        $this->assertNull($this->getConfigValue());
    }

    /**
     * Get the config value from core_config_data.
     *
     * @return string|null
     */
    private function getConfigValue(): ?string
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('core_config_data');

        $select = $connection->select()
            ->from($tableName, ['value'])
            ->where('path = ?', self::CONFIG_PATH)
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0);

        $result = $connection->fetchOne($select);

        return $result !== false ? $result : null;
    }

    /**
     * Delete the config value from core_config_data to ensure clean state.
     *
     * @return void
     */
    private function deleteConfigValue(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('core_config_data');

        $connection->delete(
            $tableName,
            [
                'path = ?' => self::CONFIG_PATH,
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
            ]
        );
    }
}
