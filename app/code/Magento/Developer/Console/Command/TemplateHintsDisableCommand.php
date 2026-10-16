<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Developer\Console\Command;

use InvalidArgumentException;
use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TemplateHintsDisableCommand extends Command
{
    public const COMMAND_NAME = 'dev:template-hints:disable';

    public const SUCCESS_MESSAGE = "Template hints disabled. Refresh cache types";

    private const CONFIG_PATH = 'dev/debug/template_hints_storefront';

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * @param ConfigInterface $resourceConfig
     * @param DeploymentConfig|null $deploymentConfig
     * @param ConfigPathResolver|null $configPathResolver
     */
    public function __construct(
        ConfigInterface $resourceConfig,
        ?DeploymentConfig $deploymentConfig = null,
        ?ConfigPathResolver $configPathResolver = null
    ) {
        parent::__construct();
        $this->resourceConfig = $resourceConfig;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        $this->configPathResolver = $configPathResolver ?: ObjectManager::getInstance()->get(ConfigPathResolver::class);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Disable frontend template hints. A cache flush might be required.');

        parent::configure();
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->isConfigLocked()) {
            $output->writeln('<error>The value you set has already been locked.'
                . ' To change the value, modify app/etc/env.php or app/etc/config.php directly.</error>');

            return Cli::RETURN_FAILURE;
        }

        $this->resourceConfig->saveConfig(self::CONFIG_PATH, 0, 'default', 0);
        $output->writeln("<info>" . self::SUCCESS_MESSAGE . "</info>");

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Check if the configuration path is locked in deployment config.
     *
     * @return bool
     */
    private function isConfigLocked(): bool
    {
        $scopePath = $this->configPathResolver->resolve(self::CONFIG_PATH, 'default', null, System::CONFIG_TYPE);

        return $this->deploymentConfig->get($scopePath) !== null;
    }
}
