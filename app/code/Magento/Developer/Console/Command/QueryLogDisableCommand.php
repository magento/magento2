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

class QueryLogDisableCommand extends Command
{
    /**
     * command name
     */
    const COMMAND_NAME = 'dev:query-log:disable';

    /**
     * File logger alias
     */
    const QUIET_LOGGER_ALIAS = 'quiet';

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
            ->setDescription('Disable DB query logging');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = ['db_logger_alias' => 'quiet'];
        $this->deploymentConfigWriter->saveConfig([ConfigFilePool::APP_ENV => $data]);

        $output->writeln("<info>DB query logging disabled.</info>");
    }
}