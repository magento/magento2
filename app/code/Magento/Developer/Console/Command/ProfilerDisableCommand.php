<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use InvalidArgumentException;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem\Io\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProfilerDisableCommand extends Command
{
    /**
     * Profiler flag file path
     */
    public const PROFILER_FLAG_FILE = 'var/profiler.flag';

    public const COMMAND_NAME = 'dev:profiler:disable';

    public const SUCCESS_MESSAGE = 'Profiler disabled.';

    /**
     * @var File
     */
    private $filesystem;

    /**
     * Initialize dependencies.
     *
     * @param File $filesystem
     * @internal param ConfigInterface $resourceConfig
     */
    public function __construct(File $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Disable the profiler.');

        parent::configure();
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem->rm(BP . '/' . self::PROFILER_FLAG_FILE);
        if (!$this->filesystem->fileExists(BP . '/' . self::PROFILER_FLAG_FILE)) {
            $output->writeln('<info>'. self::SUCCESS_MESSAGE . '</info>');
            return Cli::RETURN_SUCCESS;
        }
        $output->writeln('<error>Something went wrong while disabling the profiler.</error>');

        return Cli::RETURN_FAILURE;
    }
}
