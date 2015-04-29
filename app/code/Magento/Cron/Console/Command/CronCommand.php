<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cron\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

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
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $params = $_SERVER;
        $params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $this->objectManager = $objectManagerFactory->create($params);
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
                'Run jobs only from specified group',
                'default'
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
        $params['group'] = $input->getOption(self::INPUT_KEY_GROUP);
        // This 'standaloneProcessStarted' flag is for internal communication between processes only
        $params['standaloneProcessStarted'] = '0';
        /** @var \Magento\Framework\App\Cron $cronObserver */
        $cronObserver = $this->objectManager->create('Magento\Framework\App\Cron', ['parameters' => $params]);
        $cronObserver->launch();
        $output->writeln('<info>' . 'Ran jobs by schedule.' . '</info>');
    }
}
