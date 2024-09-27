<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SampleData\Console\Command;

use Composer\Console\ApplicationFactory;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\SampleData\Model\Dependency;
use Magento\Setup\Model\PackagesAuth;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for deployment of Sample Data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SampleDataDeployCommand extends Command
{
    public const OPTION_NO_UPDATE = 'no-update';

    /** @var Filesystem */
    private Filesystem $filesystem;

    /** @var Dependency */
    private Dependency $sampleDataDependency;

    /** @var ApplicationFactory */
    private ApplicationFactory $applicationFactory;

    /**
     * @param Filesystem $filesystem
     * @param Dependency $sampleDataDependency
     * @param ApplicationFactory $applicationFactory
     */
    public function __construct(
        Filesystem $filesystem,
        Dependency $sampleDataDependency,
        ApplicationFactory $applicationFactory
    ) {
        $this->filesystem = $filesystem;
        $this->sampleDataDependency = $sampleDataDependency;
        $this->applicationFactory = $applicationFactory;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('sampledata:deploy')
            ->setDescription('Deploy sample data modules for composer-based Magento installations');
        $this->addOption(
            self::OPTION_NO_UPDATE,
            null,
            InputOption::VALUE_NONE,
            'Update composer.json without executing composer update'
        );
        parent::configure();
    }

    /**
     * @inheritdoc
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws FileSystemException
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->updateMemoryLimit();
        $this->createAuthFile();
        $sampleDataPackages = $this->sampleDataDependency->getSampleDataPackages();
        if (!empty($sampleDataPackages)) {
            $baseDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
            $commonArgs = ['--working-dir' => $baseDir, '--no-progress' => 1];
            if ($input->getOption(self::OPTION_NO_UPDATE)) {
                $commonArgs['--no-update'] = 1;
            }
            $packages = [];
            foreach ($sampleDataPackages as $name => $version) {
                $packages[] = "$name:$version";
            }
            $commonArgs = array_merge(['packages' => $packages], $commonArgs);
            $arguments = array_merge(['command' => 'require'], $commonArgs);
            $commandInput = new ArrayInput($arguments);

            $application = $this->applicationFactory->create();
            $application->setAutoExit(false);
            $result = $application->run($commandInput, $output);
            if ($result !== 0) {
                $output->writeln(
                    '<info>' . 'There is an error during sample data deployment. Composer file will be reverted.'
                    . '</info>'
                );
                $application->resetComposer();

                return Cli::RETURN_FAILURE;
            }

            $output->writeln(
                '<info>'
                . 'Sample data modules have been added via composer. Please run bin/magento setup:upgrade'
                . '</info>'
            );

            return Cli::RETURN_SUCCESS;
        }

        $output->writeln('<info>' . 'There is no sample data for current set of modules.' . '</info>');

        return Cli::RETURN_FAILURE;
    }

    /**
     * Create new auth.json file if it doesn't exist.
     *
     * We create auth.json with correct permissions instead of relying on Composer.
     *
     * @return void
     * @throws LocalizedException
     */
    private function createAuthFile(): void
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::COMPOSER_HOME);

        if (!$directory->isExist(PackagesAuth::PATH_TO_AUTH_FILE)) {
            try {
                $directory->writeFile(PackagesAuth::PATH_TO_AUTH_FILE, '{}');
            } catch (Exception $e) {
                throw new LocalizedException(__(
                    'Error in writing Auth file %1. Please check permissions for writing.',
                    $directory->getAbsolutePath(PackagesAuth::PATH_TO_AUTH_FILE)
                ));
            }
        }
    }

    /**
     * Updates PHP memory limit
     *
     * @throws InvalidArgumentException
     * @return void
     */
    private function updateMemoryLimit(): void
    {
        if (function_exists('ini_set')) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $result = ini_set('display_errors', 1);
            if ($result === false) {
                $error = error_get_last();
                throw new InvalidArgumentException(__(
                    'Failed to set ini option display_errors to value 1. %1',
                    $error['message']
                ));
            }
            $memoryLimit = trim(ini_get('memory_limit'));
            if ($memoryLimit != -1 && $this->getMemoryInBytes($memoryLimit) < 756 * 1024 * 1024) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $result = ini_set('memory_limit', '756M');
                if ($result === false) {
                    $error = error_get_last();
                    throw new InvalidArgumentException(__(
                        'Failed to set ini option memory_limit to 756M. %1',
                        $error['message']
                    ));
                }
            }
        }
    }

    /**
     * Retrieves the memory size in bytes
     *
     * @param string $value
     * @return int
     */
    private function getMemoryInBytes(string $value): int
    {
        $unit = strtolower(substr($value, -1, 1));
        $value = (int) $value;
        switch ($unit) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
        }
        return $value;
    }
}
