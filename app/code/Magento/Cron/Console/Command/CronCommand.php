<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cron\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Cron\Observer\ProcessCronQueueObserver;
use Magento\Framework\Console\Cli;
use Magento\Framework\Shell\ComplexParameter;

/**
 * Command for executing cron jobs
 */
class CronCommand extends Command
{
    /**
     * Name of input option
     */
    const INPUT_KEY_GROUP = 'group';

    /**
     * Object manager factory
     *
     * @var ObjectManagerFactory
     */
    private $objectManagerFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $this->objectManagerFactory = $objectManagerFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_GROUP,
                null,
                InputOption::VALUE_REQUIRED,
                'Run jobs only from specified group'
            ),
            new InputOption(
                Cli::INPUT_KEY_BOOTSTRAP,
                null,
                InputOption::VALUE_REQUIRED,
                'Add or override parameters of the bootstrap'
            ),
        ];
        $this->setName('cron:run')
            ->setDescription('Runs jobs by schedule')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $omParams = $_SERVER;
        $omParams[StoreManager::PARAM_RUN_CODE] = 'admin';
        $omParams[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $objectManager = $this->objectManagerFactory->create($omParams);

        $params[self::INPUT_KEY_GROUP] = $input->getOption(self::INPUT_KEY_GROUP);
        $params[ProcessCronQueueObserver::STANDALONE_PROCESS_STARTED] = '0';
        $bootstrap = $input->getOption(Cli::INPUT_KEY_BOOTSTRAP);
        if ($bootstrap) {
            $bootstrapProcessor = new ComplexParameter(Cli::INPUT_KEY_BOOTSTRAP);
            $bootstrapOptionValues = $bootstrapProcessor->getFromString(
                '--' . Cli::INPUT_KEY_BOOTSTRAP . '=' . $bootstrap
            );
            $bootstrapOptionValue = $bootstrapOptionValues[ProcessCronQueueObserver::STANDALONE_PROCESS_STARTED];
            if ($bootstrapOptionValue) {
                $params[ProcessCronQueueObserver::STANDALONE_PROCESS_STARTED] = $bootstrapOptionValue;
            }
        }
        /** @var \Magento\Framework\App\Cron $cronObserver */
        $cronObserver = $objectManager->create('Magento\Framework\App\Cron', ['parameters' => $params]);
        $cronObserver->launch();
        $output->writeln('<info>' . 'Ran jobs by schedule.' . '</info>');
    }
}
