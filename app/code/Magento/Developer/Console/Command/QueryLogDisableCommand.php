<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use InvalidArgumentException;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\DB\Logger\LoggerProxy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueryLogDisableCommand extends Command
{
    public const COMMAND_NAME = 'dev:query-log:disable';

    public const SUCCESS_MESSAGE = "DB query logging disabled.";

    /**
     * @var Writer
     */
    private $deployConfigWriter;

    /**
     * QueryLogDisableCommand constructor.
     * @param Writer $deployConfigWriter
     * @param ?string $name
     */
    public function __construct(
        Writer $deployConfigWriter,
        $name = null
    ) {
        parent::__construct($name);
        $this->deployConfigWriter = $deployConfigWriter;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Disable DB query logging');

        parent::configure();
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = [LoggerProxy::PARAM_ALIAS => LoggerProxy::LOGGER_ALIAS_DISABLED];
        $this->deployConfigWriter->saveConfig([ConfigFilePool::APP_ENV => [LoggerProxy::CONF_GROUP_NAME => $data]]);

        $output->writeln("<info>". self::SUCCESS_MESSAGE . "</info>");

        return Cli::RETURN_SUCCESS;
    }
}
