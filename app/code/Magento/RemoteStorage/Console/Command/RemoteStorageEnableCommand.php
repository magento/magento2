<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Console\Command;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverPool;
use Magento\RemoteStorage\Driver\DriverFactoryPool;
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
    private const ARGUMENT_BUCKET = 'bucket';
    private const ARGUMENT_REGION = 'region';
    private const ARGUMENT_ACCESS_KEY = 'access-key';
    private const ARGUMENT_SECRET_KEY = 'secret-key';
    private const ARGUMENT_PREFIX = 'prefix';
    private const OPTION_IS_PUBLIC = 'is-public';

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var DriverFactoryPool
     */
    private $driverFactoryPool;

    /**
     * @param Writer $writer
     * @param DriverFactoryPool $driverFactoryPool
     */
    public function __construct(Writer $writer, DriverFactoryPool $driverFactoryPool)
    {
        $this->writer = $writer;
        $this->driverFactoryPool = $driverFactoryPool;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Enable remote storage integration')
            ->addArgument(self::ARG_DRIVER, InputArgument::OPTIONAL, 'Remote driver', DriverPool::FILE)
            ->addArgument(self::ARGUMENT_BUCKET, InputArgument::OPTIONAL, 'Bucket')
            ->addArgument(self::ARGUMENT_REGION, InputArgument::OPTIONAL, 'Region')
            ->addArgument(self::ARGUMENT_PREFIX, InputArgument::OPTIONAL, 'Prefix', '')
            ->addArgument(self::ARGUMENT_ACCESS_KEY, InputArgument::OPTIONAL, 'Access key')
            ->addArgument(self::ARGUMENT_SECRET_KEY, InputArgument::OPTIONAL, 'Secret key')
            ->addOption(self::OPTION_IS_PUBLIC, null, InputOption::VALUE_REQUIRED, 'Is public', false);
    }

    /**
     * Executes command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $driver = $input->getArgument(self::ARG_DRIVER);

        if ($driver === DriverPool::FILE) {
            $output->writeln(sprintf(
                'Driver "%s" was specified. Skipping',
                $driver
            ));

            return Cli::RETURN_SUCCESS;
        }

        if (!$this->driverFactoryPool->has($driver)) {
            $output->writeln('Driver %s was not found', $driver);

            return Cli::RETURN_FAILURE;
        }

        $prefix = (string)$input->getArgument(self::ARGUMENT_PREFIX);
        $config = [
            'bucket' => (string)$input->getArgument(self::ARGUMENT_BUCKET),
            'region' => (string)$input->getArgument(self::ARGUMENT_REGION),
        ];
        $isPublic = (bool)$input->getOption(self::OPTION_IS_PUBLIC);

        if (($key = (string)$input->getArgument(self::ARGUMENT_ACCESS_KEY))
            && ($secret = (string)$input->getArgument(self::ARGUMENT_SECRET_KEY))
        ) {
            $config['credentials']['key'] = $key;
            $config['credentials']['secret'] = $secret;
        }

        try {
            $this->driverFactoryPool->get($driver)->create($config, $prefix);
        } catch (\Exception $exception) {
            $output->writeln(sprintf(
                '<error>Config cannot be set: %s</error>',
                $exception->getMessage()
            ));

            return Cli::RETURN_FAILURE;
        }

        $this->writer->saveConfig([
            ConfigFilePool::APP_ENV => [
                'remote_storage' => [
                    'driver' => $driver,
                    'prefix' => $prefix,
                    'is_public' => $isPublic,
                    'config' => $config
                ]
            ]
        ], true);

        $output->writeln(sprintf(
            '<info>Config for driver "%s" was saved.</info>',
            $driver
        ));

        return Cli::RETURN_SUCCESS;
    }
}
