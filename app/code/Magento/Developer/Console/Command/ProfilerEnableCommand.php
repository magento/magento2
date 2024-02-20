<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use InvalidArgumentException;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Profiler\Driver\Standard\Output\Csvfile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProfilerEnableCommand extends Command
{
    /**
     * Profiler flag file path
     */
    public const PROFILER_FLAG_FILE = 'var/profiler.flag';

    /**
     * Profiler type default setting
     */
    public const TYPE_DEFAULT = 'html';

    /**
     * Built in profiler types
     */
    public const BUILT_IN_TYPES = ['html', 'csvfile'];

    public const COMMAND_NAME = 'dev:profiler:enable';

    public const SUCCESS_MESSAGE = 'Profiler enabled with %s output.';

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
            ->setDescription('Enable the profiler.')
            ->addArgument('type', InputArgument::OPTIONAL, 'Profiler type');

        parent::configure();
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
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
                        Csvfile::DEFAULT_FILEPATH
                    )
                    . '</info>'
                );
            }
            $output->write(PHP_EOL);

            return Cli::RETURN_SUCCESS;
        }
        $output->writeln('<error>Something went wrong while enabling the profiler.</error>');

        return Cli::RETURN_FAILURE;
    }
}
