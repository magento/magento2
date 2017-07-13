<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\SensitiveConfigSet;

use Magento\Deploy\Console\Command\App\SensitiveConfigSetCommand;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Deploy\Model\ConfigWriter;
use Magento\Framework\App\Config\CommentParserInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\LocalizedException;

/**
 * Processes the sensitive:config:set command.
 */
class SensitiveConfigSetFacade
{
    /**
     * The parser for config comments.
     *
     * @var CommentParserInterface
     */
    private $commentParser;

    /**
     * The pool of configs.
     *
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * The config writer.
     *
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * The validator for scopes.
     *
     * @var ValidatorInterface
     */
    private $scopeValidator;

    /**
     * The factory of config collectors.
     *
     * @var CollectorFactory
     */
    private $collectorFactory;

    /**
     * @param ConfigFilePool $configFilePool The pool of configs
     * @param CommentParserInterface $commentParser The parser for config comments
     * @param ConfigWriter $configWriter The config writer
     * @param ValidatorInterface $scopeValidator The validator for scopes
     * @param CollectorFactory $collectorFactory The factory of config collectors
     */
    public function __construct(
        ConfigFilePool $configFilePool,
        CommentParserInterface $commentParser,
        ConfigWriter $configWriter,
        ValidatorInterface $scopeValidator,
        CollectorFactory $collectorFactory
    ) {
        $this->commentParser = $commentParser;
        $this->configFilePool = $configFilePool;
        $this->configWriter = $configWriter;
        $this->scopeValidator = $scopeValidator;
        $this->collectorFactory = $collectorFactory;
    }

    /**
     * Processes the config:sensitive:set command.
     *
     * @param InputInterface $input The input manager
     * @param OutputInterface $output The output manager
     * @return void
     * @throws LocalizedException If scope or scope code is not valid
     * @throws RuntimeException If sensitive config can not be filled
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $scope = $input->getOption(SensitiveConfigSetCommand::INPUT_OPTION_SCOPE);
        $scopeCode = $input->getOption(SensitiveConfigSetCommand::INPUT_OPTION_SCOPE_CODE);
        $isInteractive = $input->getOption(SensitiveConfigSetCommand::INPUT_OPTION_INTERACTIVE);

        $this->scopeValidator->isValid($scope, $scopeCode);
        $configPaths = $this->getConfigPaths();
        $collector = $this->collectorFactory->create(
            $isInteractive ? CollectorFactory::TYPE_INTERACTIVE : CollectorFactory::TYPE_SIMPLE
        );
        $values = $collector->getValues($input, $output, $configPaths);

        $this->configWriter->save($values, $scope, $scopeCode);

        $output->writeln(sprintf(
            '<info>Configuration value%s saved in app/etc/%s</info>',
            $isInteractive ? 's' : '',
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV)
        ));
    }

    /**
     * Get sensitive configuration paths.
     *
     * @return array
     * @throws LocalizedException if configuration file not exists or sensitive configuration is empty
     */
    private function getConfigPaths()
    {
        $configFilePath = $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG);
        try {
            $configPaths = $this->commentParser->execute($configFilePath);
        } catch (FileSystemException $e) {
            throw new RuntimeException(__(
                'File app/etc/%1 can\'t be read. Please check if it exists and has read permissions.',
                [
                    $configFilePath
                ]
            ));
        }

        if (empty($configPaths)) {
            throw new RuntimeException(__('There are no sensitive configurations to fill'));
        }

        return $configPaths;
    }
}
