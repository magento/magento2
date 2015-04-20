<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\DeploymentConfig;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Command for deploy static content
 */
class DeployStaticContentCommand extends Command
{
    /**
     * Key for dry-run option
     */
    const DRY_RUN_OPTION = 'dry-run';

    /**
     * Key for languages parameter
     */
    const LANGUAGE_OPTION = 'languages';

    /**
     * Object manager provider
     *
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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:static-content:deploy')
            ->setDescription('Deploys static view files')
            ->setDefinition([
                new InputOption(
                    self::DRY_RUN_OPTION,
                    '-d',
                    InputOption::VALUE_NONE,
                    'If specified, then no files will be actually deployed.'
                ),
                new InputArgument(
                    self::LANGUAGE_OPTION,
                    InputArgument::IS_ARRAY,
                    'List of languages you want the tool populate files for.',
                    ['en_US']
                ),
            ]);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln("<info>You need to install the Magento application before running this utility.</info>");
            return;
        }

        $options = $input->getOptions();

        $languages = $input->getArgument(self::LANGUAGE_OPTION);
        foreach ($languages as $lang) {
            if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $lang)) {
                throw new \InvalidArgumentException(
                    $lang . ' argument has invalid value format'
                );
            }
        }

        try {
            $objectManager = $this->objectManagerProvider->get();

            // run the deployment logic
            $filesUtil = $objectManager->create(
                '\Magento\Framework\App\Utility\Files',
                ['pathToSource' => BP]
            );

            $objectManagerFactory = $this->objectManagerProvider->getObjectManagerFactory();

            /** @var \Magento\Setup\Model\Deployer $deployer */
            $deployer = $objectManager->create(
                'Magento\Setup\Model\Deployer',
                ['filesUtil' => $filesUtil, 'output' => $output, 'isDryRun' => $options[self::DRY_RUN_OPTION]]
            );
            $deployer->deploy($objectManagerFactory, $languages);

        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>>');
            if ($output->isVerbose()) {
                $output->writeln($e->getTraceAsString());
            }
            return;
        }
    }
}
