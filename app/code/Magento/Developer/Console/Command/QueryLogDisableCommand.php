<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\DB\Logger\LoggerProxy;

/**
 * Class \Magento\Developer\Console\Command\QueryLogDisableCommand
 *
 * @since 2.2.0
 */
class QueryLogDisableCommand extends Command
{
    /**
     * command name
     */
    const COMMAND_NAME = 'dev:query-log:disable';

    /**
     * Success message
     */
    const SUCCESS_MESSAGE = "DB query logging disabled.";

    /**
     * @var Writer
     * @since 2.2.0
     */
    private $deployConfigWriter;

    /**
     * QueryLogDisableCommand constructor.
     * @param Writer $deployConfigWriter
     * @param null $name
     * @since 2.2.0
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
     * @since 2.2.0
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Disable DB query logging');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = [LoggerProxy::PARAM_ALIAS => LoggerProxy::LOGGER_ALIAS_DISABLED];
        $this->deployConfigWriter->saveConfig([ConfigFilePool::APP_ENV => [LoggerProxy::CONF_GROUP_NAME => $data]]);

        $output->writeln("<info>". self::SUCCESS_MESSAGE . "</info>");
    }
}
