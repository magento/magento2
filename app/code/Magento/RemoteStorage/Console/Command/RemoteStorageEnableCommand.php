<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Console\Command;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remote storage configuration enablement.
 */
class RemoteStorageEnableCommand extends Command
{
    private const NAME = 'remote-storage:enable';
    private const ARG_DRIVER = 'driver';
    private const OPTION_BUCKET = 'bucket';
    private const OPTION_REGION = 'region';
    private const OPTION_ACCESS_KEY = 'access-key';
    private const OPTION_SECRET_KEY = 'secret-key';
    private const OPTION_PREFIX = 'prefix';
    private const OPTION_IS_PUBLIC = 'is-public';

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @param Writer $writer
     */
    public function __construct(Writer $writer)
    {
        $this->writer = $writer;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Enable remote storage integration')
            ->addArgument(self::ARG_DRIVER, InputArgument::REQUIRED, 'Remote driver')
            ->addOption(self::OPTION_BUCKET, null, InputOption::VALUE_REQUIRED, 'Bucket')
            ->addOption(self::OPTION_REGION, null, InputOption::VALUE_REQUIRED, 'Region')
            ->addOption(self::OPTION_ACCESS_KEY, null, InputOption::VALUE_REQUIRED, 'Access key')
            ->addOption(self::OPTION_SECRET_KEY, null, InputOption::VALUE_REQUIRED, 'Secret key')
            ->addOption(self::OPTION_PREFIX, null, InputOption::VALUE_REQUIRED, 'Prefix', '')
            ->addOption(self::OPTION_IS_PUBLIC, null, InputOption::VALUE_NONE, 'Is public');
    }

    /**
     * Executes command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws FileSystemException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writer->saveConfig([
            ConfigFilePool::APP_ENV => [
                'remote_storage' => [
                    'driver' => (string)$input->getArgument(self::ARG_DRIVER),
                    'bucket' => (string)$input->getOption(self::OPTION_BUCKET),
                    'region' => (string)$input->getOption(self::OPTION_REGION),
                    'access_key' => (string)$input->getOption(self::OPTION_ACCESS_KEY),
                    'secret_key' => (string)$input->getOption(self::OPTION_SECRET_KEY),
                    'prefix' => (string)$input->getOption(self::OPTION_PREFIX),
                    'is_public' => (bool)$input->getOption(self::OPTION_IS_PUBLIC)
                ]
            ]
        ], true);

        $output->writeln('<info>Config was saved.</info>');
    }
}
