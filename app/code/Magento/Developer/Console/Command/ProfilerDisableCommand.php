<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Magento\Framework\Filesystem\Io\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Command to disable Magento profiler.
 */
class ProfilerDisableCommand extends Command
{
    /**
     * Profiler flag file
     */
    const PROFILER_FLAG_FILE = 'var/profiler.flag';

    /**
     * Command name
     */
    const COMMAND_NAME = 'dev:profiler:disable';

    /**
     * Success message
     */
    const SUCCESS_MESSAGE = 'Profiler disabled.';

    /**
     * @var File
     */
    private $filesystem;

    /**
     * Initialize dependencies.
     *
     * @param File $filesystem
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     * @internal param ConfigInterface $resourceConfig
     */
    public function __construct(File $filesystem, $name = null)
    {
        parent::__construct($name ?: self::COMMAND_NAME);
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Disable the profiler.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem->rm(BP . '/' . self::PROFILER_FLAG_FILE);
        if (!$this->filesystem->fileExists(BP . '/' . self::PROFILER_FLAG_FILE)) {
            $output->writeln('<info>'. self::SUCCESS_MESSAGE . '</info>');
            return;
        }
        $output->writeln('<error>Something went wrong while disabling the profiler.</error>');
    }
}
