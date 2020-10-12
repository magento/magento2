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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remote storage configuration disablement.
 */
class RemoteStorageDisableCommand extends Command
{
    private const NAME = 'remote-storage:disable';

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
            ->setDescription('Disable remote storage');
    }

    /**
     * Executes command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws FileSystemException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->writer->saveConfig([
            ConfigFilePool::APP_ENV => [
                'remote_storage' => [
                    'driver' => DriverPool::FILE,
                ]
            ]
        ], true);

        $output->writeln('<info>Config was saved.</info>');

        return Cli::RETURN_SUCCESS;
    }
}
