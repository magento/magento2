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
use Symfony\Component\Console\Input\InputArgument;

class ProfilerEnableCommand extends Command
{
    /**
     * Profiler flag file
     */
    const PROFILER_FLAG_FILE = 'var/profiler.flag';

    /**
     * Profiler type default setting
     */
    const TYPE_DEFAULT = 'html';

    /**
     * Built in profiler types
     */
    const BUILT_IN_TYPES = ['html', 'csvfile'];

    /**
     * Command name
     */
    const COMMAND_NAME = 'dev:profiler:enable';

    /**
     * Success message
     */
    const SUCCESS_MESSAGE = 'Profiler enabled with %s output.';

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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Enable the profiler.')
            ->addArgument('type', InputArgument::OPTIONAL, 'Profiler type');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        if (!$type) {
            $type = self::TYPE_DEFAULT;
        }

        if (!in_array($type, self::BUILT_IN_TYPES, true)) {
            $builtInTypes = implode(', ', self::BUILT_IN_TYPES);
            $output->writeln(
                '<comment>'
                . sprintf('Type %s is not one of the built-in output types (%s).', $type, $builtInTypes) .
                '</comment>'
            );
        }

        $this->filesystem->write(BP . '/' . self::PROFILER_FLAG_FILE, $type);
        if ($this->filesystem->fileExists(BP . '/' . self::PROFILER_FLAG_FILE)) {
            $output->write('<info>'. sprintf(self::SUCCESS_MESSAGE, $type) . '</info>');
            if ($type == 'csvfile') {
                $output->write(
                    '<info> ' . sprintf(
                        'Output will be saved in %s',
                        \Magento\Framework\Profiler\Driver\Standard\Output\Csvfile::DEFAULT_FILEPATH
                    )
                    . '</info>'
                );
            }
            $output->write(PHP_EOL);

            return;
        }
        $output->writeln('<error>Something went wrong while enabling the profiler.</error>');
    }
}
