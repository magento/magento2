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
use Magento\Framework\Validator\Locale;

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
     * @var Locale
     */
    private $validator;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param DeploymentConfig $deploymentConfig
     * @param Locale $validator
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        DeploymentConfig $deploymentConfig,
        Locale $validator
    ) {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->deploymentConfig = $deploymentConfig;
        $this->validator = $validator;
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

            if (!$this->validator->isValid($lang)) {
                throw new \InvalidArgumentException(
                    $lang . ' argument has invalid value, please run info:language:list for list of available locales'
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
