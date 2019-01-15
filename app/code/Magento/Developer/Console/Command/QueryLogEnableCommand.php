<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Magento\Framework\DB\Logger\LoggerProxy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;

class QueryLogEnableCommand extends Command
{
    /**
     * input parameter log-all-queries
     */
    const INPUT_ARG_LOG_ALL_QUERIES = 'include-all-queries';

    /**
     * input parameter log-query-time
     */
    const INPUT_ARG_LOG_QUERY_TIME = 'query-time-threshold';

    /**
     * input parameter log-call-stack
     */
    const INPUT_ARG_LOG_CALL_STACK = 'include-call-stack';

    /**
     * command name
     */
    const COMMAND_NAME = 'dev:query-log:enable';

    /**
     * Success message
     */
    const SUCCESS_MESSAGE = "DB query logging enabled.";

    /**
     * @var Writer
     */
    private $deployConfigWriter;

    /**
     * QueryLogEnableCommand constructor.
     * @param Writer $deployConfigWriter
     * @param null $name
     */
    public function __construct(
        Writer $deployConfigWriter,
        $name = null
    ) {
        parent::__construct($name);
        $this->deployConfigWriter = $deployConfigWriter;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Enable DB query logging')
            ->setDefinition(
                [
                    new InputOption(
                        self::INPUT_ARG_LOG_ALL_QUERIES,
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Log all queries. [true|false]',
                        "true"
                    ),
                    new InputOption(
                        self::INPUT_ARG_LOG_QUERY_TIME,
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Query time thresholds.',
                        "0.001"
                    ),
                    new InputOption(
                        self::INPUT_ARG_LOG_CALL_STACK,
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Include call stack. [true|false]',
                        "true"
                    ),
                ]
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = [LoggerProxy::PARAM_ALIAS => LoggerProxy::LOGGER_ALIAS_FILE];

        $logAllQueries = $input->getOption(self::INPUT_ARG_LOG_ALL_QUERIES);
        $logQueryTime = $input->getOption(self::INPUT_ARG_LOG_QUERY_TIME);
        $logCallStack = $input->getOption(self::INPUT_ARG_LOG_CALL_STACK);

        $data[LoggerProxy::PARAM_LOG_ALL] = (int)($logAllQueries != 'false');
        $data[LoggerProxy::PARAM_QUERY_TIME] = number_format($logQueryTime, 3);
        $data[LoggerProxy::PARAM_CALL_STACK] = (int)($logCallStack != 'false');

        $configGroup[LoggerProxy::CONF_GROUP_NAME] = $data;

        $this->deployConfigWriter->saveConfig([ConfigFilePool::APP_ENV => $configGroup]);

        $output->writeln("<info>". self::SUCCESS_MESSAGE . "</info>");
    }
}
