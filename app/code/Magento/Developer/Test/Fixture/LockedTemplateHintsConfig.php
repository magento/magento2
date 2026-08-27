<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Developer\Test\Fixture;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class LockedTemplateHintsConfig implements RevertibleDataFixtureInterface
{
    /**
     * @var FileReader
     */
    private FileReader $reader;

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
     * @var DataObjectFactory
     */
    private DataObjectFactory $dataObjectFactory;

    /**
     * @param FileReader $reader
     * @param Writer $writer
     * @param Filesystem $filesystem
     * @param ConfigFilePool $configFilePool
     * @param ReinitableConfigInterface $appConfig
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        FileReader $reader,
        Writer $writer,
        Filesystem $filesystem,
        ConfigFilePool $configFilePool,
        ReinitableConfigInterface $appConfig,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->filesystem = $filesystem;
        $this->configFilePool = $configFilePool;
        $this->appConfig = $appConfig;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $previousConfig = $this->reader->load(ConfigFilePool::APP_ENV);
        $this->writer->saveConfig(
            [
                ConfigFilePool::APP_ENV => [
                    'system' => [
                        'default' => [
                            'dev' => [
                                'debug' => [
                                    'template_hints_storefront' => $data['value'] ?? '1'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );
        $this->appConfig->reinit();

        return $this->dataObjectFactory->create(['data' => ['previous_config' => $previousConfig]]);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => $data->getData('previous_config')]);
        $this->appConfig->reinit();
    }
}
