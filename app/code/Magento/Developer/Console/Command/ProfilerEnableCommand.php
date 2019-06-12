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

<<<<<<< HEAD
/**
 * CLI Command to enable Magento profiler.
 */
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     * @internal param ConfigInterface $resourceConfig
     */
    public function __construct(File $filesystem, $name = null)
    {
        parent::__construct($name ?: self::COMMAND_NAME);
=======
     * @internal param ConfigInterface $resourceConfig
     */
    public function __construct(File $filesystem)
    {
        parent::__construct();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
<<<<<<< HEAD
        $this->setDescription('Enable the profiler.')
            ->addArgument('type', InputArgument::OPTIONAL, 'Profiler type', self::TYPE_DEFAULT);
=======
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Enable the profiler.')
            ->addArgument('type', InputArgument::OPTIONAL, 'Profiler type');
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
<<<<<<< HEAD
        if (!in_array($type, self::BUILT_IN_TYPES)) {
            $builtInTypes = implode(', ', self::BUILT_IN_TYPES);
            $output->writeln(
                '<comment>' . sprintf('Type %s is not one of the built-in output types (%s).', $type) .
                sprintf('Make sure the necessary class exists.', $type, $builtInTypes) . '</comment>'
=======
        if (!$type) {
            $type = self::TYPE_DEFAULT;
        }

        if (!in_array($type, self::BUILT_IN_TYPES, true)) {
            $builtInTypes = implode(', ', self::BUILT_IN_TYPES);
            $output->writeln(
                '<comment>'
                . sprintf('Type %s is not one of the built-in output types (%s).', $type, $builtInTypes) .
                '</comment>'
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
            return;
        }

=======

            return;
        }
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $output->writeln('<error>Something went wrong while enabling the profiler.</error>');
    }
}
