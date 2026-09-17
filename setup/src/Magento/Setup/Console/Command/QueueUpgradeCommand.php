<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Console\Cli;
use Magento\Framework\MessageQueue\Topology\SynchronizerInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for applying message queue topology without a full setup:upgrade
 */
class QueueUpgradeCommand extends AbstractSetupCommand
{
    public const NAME = 'setup:queue:upgrade';

    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider, DeploymentConfig $deploymentConfig)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Applies message queue topology to the configured backends');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln("<info>No information is available: the Magento application is not installed.</info>");
            return Cli::RETURN_FAILURE;
        }

        /**
         * As QueueUpgradeCommand is in setup and SynchronizerInterface is part of the framework, we can`t
         * configure this command with dependency injection and we need to resolve it manually
         */
        $synchronizer = $this->objectManagerProvider->get()->get(SynchronizerInterface::class);

        try {
            $applied = $synchronizer->synchronize();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        if (empty($applied)) {
            $output->writeln('<info>No message queue topology needed to be applied.</info>');
        } else {
            foreach ($applied as $description) {
                $output->writeln('<info>' . $description . '</info>');
            }
        }

        return Cli::RETURN_SUCCESS;
    }
}
