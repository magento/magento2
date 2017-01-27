<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Config\App\Config\Type\System;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\App\Config\MetadataProcessor;
use Magento\Framework\App\Config\ConfigPathResolver;

/**
 * Processes file lock flow of config:set command.
 * This processor saves the value of configuration and lock it for editing in Admin interface.
 *
 * {@inheritdoc}
 */
class LockProcessor implements ConfigSetProcessorInterface
{
    /**
     * @var Writer
     */
    private $deploymentConfigWriter;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var MetadataProcessor
     */
    private $metadataProcessor;

    /**
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * @param Writer $writer
     * @param ArrayManager $arrayManager
     * @param MetadataProcessor $metadataProcessor
     * @param ConfigPathResolver $configPathResolver
     */
    public function __construct(
        Writer $writer,
        ArrayManager $arrayManager,
        MetadataProcessor $metadataProcessor,
        ConfigPathResolver $configPathResolver
    ) {
        $this->deploymentConfigWriter = $writer;
        $this->arrayManager = $arrayManager;
        $this->metadataProcessor = $metadataProcessor;
        $this->configPathResolver = $configPathResolver;
    }

    /**
     * Processes lock flow of config:set command.
     * Requires read access to filesystem.
     *
     * {@inheritdoc}
     */
    public function process(InputInterface $input)
    {
        $path = $input->getArgument(ConfigSetCommand::ARG_PATH);
        $value = $input->getArgument(ConfigSetCommand::ARG_VALUE);
        $scope = $input->getOption(ConfigSetCommand::OPTION_SCOPE);
        $scopeCode = $input->getOption(ConfigSetCommand::OPTION_SCOPE_CODE);
        $configPath = $this->configPathResolver->resolve($path, $scope, $scopeCode, System::CONFIG_TYPE);

        $value = $this->metadataProcessor->prepareValue($value, $path);

        try {
            $this->deploymentConfigWriter->saveConfig(
                [
                    ConfigFilePool::APP_CONFIG => $this->arrayManager->set($configPath, [], $value)
                ],
                true
            );
        } catch (FileSystemException $exception) {
            throw new CouldNotSaveException(__('%1', $exception->getMessage()), $exception);
        }
    }
}
