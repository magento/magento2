<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;

class QueryLogEnableCommand extends Command
{
    /**
     * input parameter log-all-queries
     */
    const INPUT_ARG_LOG_ALL_QUERIES = 'log-all-queries';

    /**
     * input parameter log-query-time
     */
    const INPUT_ARG_LOG_QUERY_TIME = 'log-query-time';

    /**
     * input parameter log-call-stack
     */
    const INPUT_ARG_LOG_CALL_STACK = 'log-call-stack';

    /**
     * command name
     */
    const COMMAND_NAME = 'dev:query-log:enable';

    /**
     * File logger alias
     */
    const FILE_LOGGER_ALIAS = 'file';

    /**
     * @var Writer
     */
    private $deploymentConfigWriter;

    public function __construct(
        Writer $deploymentConfigWriter,
        $name = null
    )
    {
        parent::__construct($name);
        $this->deploymentConfigWriter = $deploymentConfigWriter;
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Enable DB query logging');

        $this->addArgument(
            self::INPUT_ARG_LOG_ALL_QUERIES,
            InputArgument::OPTIONAL,
            'Log all queries.',
            'false'
        );

        $this->addArgument(
            self::INPUT_ARG_LOG_QUERY_TIME,
            InputArgument::OPTIONAL,
            'Query time.',
            '0.05'
        );

        $this->addArgument(
            self::INPUT_ARG_LOG_CALL_STACK,
            InputArgument::OPTIONAL,
            'Log call stack.',
            'false'
        );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = ['db_logger_alias' => 'file'];
        $this->deploymentConfigWriter->saveConfig([ConfigFilePool::APP_ENV => $data]);

//        $data = [
//            'db_logger' => [
//                'logger_alias' => 'file',
//                'log_all_queries' => false,
//                'log_query_time' => '0.05',
//                'log_call_stack' => false,
//            ]
//        ];
//        $this->deploymentConfigWriter->saveConfig([ConfigFilePool::APP_ENV => $data]);

        $output->writeln("<info>DB query logging enabled.</info>");
    }
}