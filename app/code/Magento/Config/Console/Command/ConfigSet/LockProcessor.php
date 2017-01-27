<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Symfony\Component\Console\Input\InputInterface;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\App\Config\MetadataProcessor;
use Magento\Framework\App\Config\ScopePathResolver;

/**
 * Processes file lock flow of config:set command.
 * This processor saves the value of configuration and lock it for editing in Admin interface.
 *
 * {@inheritdoc}
 */
class LockProcessor implements ConfigSetProcessorInterface
{
    /**
     * The deployment config writer.
     *
     * @var Writer
     */
    private $deploymentConfigWriter;

    /**
     * An array manager.
     *
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * The metadata processor.
     *
     * @var MetadataProcessor
     */
    private $metadataProcessor;

    /**
     * The scope path resolver.
     *
     * @var ScopePathResolver
     */
    private $scopePathResolver;

    /**
     * @param Writer $writer The deployment config writer
     * @param ArrayManager $arrayManager An array manager
     * @param MetadataProcessor $metadataProcessor The metadata processor
     * @param ScopePathResolver $scopePathResolver The scope path resolver
     */
    public function __construct(
        Writer $writer,
        ArrayManager $arrayManager,
        MetadataProcessor $metadataProcessor,
        ScopePathResolver $scopePathResolver
    ) {
        $this->deploymentConfigWriter = $writer;
        $this->arrayManager = $arrayManager;
        $this->metadataProcessor = $metadataProcessor;
        $this->scopePathResolver = $scopePathResolver;
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
        $scopePath = $this->scopePathResolver->resolve($path, $scope, $scopeCode, 'system');

        $value = $this->metadataProcessor->prepareValue($value, $path);

        try {
            $this->deploymentConfigWriter->saveConfig(
                [
                    ConfigFilePool::APP_CONFIG => $this->arrayManager->set($scopePath, [], $value)
                ],
                true
            );
        } catch (FileSystemException $exception) {
            throw new CouldNotSaveException(__('Filesystem is not writable.'));
        }
    }
}
