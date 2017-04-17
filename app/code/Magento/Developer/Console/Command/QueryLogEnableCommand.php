<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Magento\Framework\DB\Logger\LoggerProxy;
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
            ->setDescription('Enable DB query logging');

        $this->addArgument(
            self::INPUT_ARG_LOG_ALL_QUERIES,
            InputArgument::OPTIONAL,
            'Log all queries. Options: "true" or "false"',
            'true'
        );

        $this->addArgument(
            self::INPUT_ARG_LOG_QUERY_TIME,
            InputArgument::OPTIONAL,
            'Log query time.',
            '0.001'
        );

        $this->addArgument(
            self::INPUT_ARG_LOG_CALL_STACK,
            InputArgument::OPTIONAL,
            'Log call stack. Options: "true" or "false"',
            'true'
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

        $logAllQueries = $input->getArgument(self::INPUT_ARG_LOG_ALL_QUERIES);
        $logQueryTime = $input->getArgument(self::INPUT_ARG_LOG_QUERY_TIME);
        $logCallStack = $input->getArgument(self::INPUT_ARG_LOG_CALL_STACK);

        $data[LoggerProxy::PARAM_LOG_ALL] = (int)($logAllQueries != 'false');
        $data[LoggerProxy::PARAM_QUERY_TIME] = number_format($logQueryTime, 3);
        $data[LoggerProxy::PARAM_CALL_STACK] = (int)($logCallStack != 'false');

        $this->deployConfigWriter->saveConfig([ConfigFilePool::APP_ENV => $data]);

        $output->writeln("<info>". self::SUCCESS_MESSAGE . "</info>");
    }
}
