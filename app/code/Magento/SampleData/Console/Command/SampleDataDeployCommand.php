<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SampleData\Console\Command;

use Composer\Console\Application;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Model\PackagesAuth;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for deployment of Sample Data
 */
class SampleDataDeployCommand extends Command
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\SampleData\Model\Dependency
     */
    private $sampleDataDependency;

    /**
     * @var \Symfony\Component\Console\Input\ArrayInputFactory
     * @deprecated 2.1.0
     */
    private $arrayInputFactory;

    /**
     * @var \Composer\Console\ApplicationFactory
     */
    private $applicationFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\SampleData\Model\Dependency $sampleDataDependency
     * @param \Symfony\Component\Console\Input\ArrayInputFactory $arrayInputFactory
     * @param \Composer\Console\ApplicationFactory $applicationFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\SampleData\Model\Dependency $sampleDataDependency,
        \Symfony\Component\Console\Input\ArrayInputFactory $arrayInputFactory,
        \Composer\Console\ApplicationFactory $applicationFactory
    ) {
        $this->filesystem = $filesystem;
        $this->sampleDataDependency = $sampleDataDependency;
        $this->arrayInputFactory = $arrayInputFactory;
        $this->applicationFactory = $applicationFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sampledata:deploy')
            ->setDescription('Deploy sample data modules');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->updateMemoryLimit();
        $this->createAuthFile();
        $sampleDataPackages = $this->sampleDataDependency->getSampleDataPackages();
        if (!empty($sampleDataPackages)) {
            $baseDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
            $commonArgs = ['--working-dir' => $baseDir, '--no-progress' => 1];
            $packages = [];
            foreach ($sampleDataPackages as $name => $version) {
                $packages[] = "$name:$version";
            }
            $commonArgs = array_merge(['packages' => $packages], $commonArgs);
            $arguments = array_merge(['command' => 'require'], $commonArgs);
            $commandInput = new ArrayInput($arguments);

            /** @var Application $application */
            $application = $this->applicationFactory->create();
            $application->setAutoExit(false);
            $result = $application->run($commandInput, $output);
            if ($result !== 0) {
                $output->writeln(
                    '<info>' . 'There is an error during sample data deployment. Composer file will be reverted.'
                    . '</info>'
                );
                $application->resetComposer();
            }
        } else {
            $output->writeln('<info>' . 'There is no sample data for current set of modules.' . '</info>');
        }
    }

    /**
     * Create new auth.json file if it doesn't exist.
     *
     * We create auth.json with correct permissions instead of relying on Composer.
     *
     * @return void
     * @throws \Exception
     * @since 2.1.3
     */
    private function createAuthFile()
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::COMPOSER_HOME);

        if (!$directory->isExist(PackagesAuth::PATH_TO_AUTH_FILE)) {
            try {
                $directory->writeFile(PackagesAuth::PATH_TO_AUTH_FILE, '{}');
            } catch (\Exception $e) {
                $message = 'Error in writing Auth file '
                    . $directory->getAbsolutePath(PackagesAuth::PATH_TO_AUTH_FILE)
                    . '. Please check permissions for writing.';
                throw new \Exception($message);
            }
        }
    }

    /**
     * @return void
     * @since 2.1.0
     */
    private function updateMemoryLimit()
    {
        if (function_exists('ini_set')) {
            @ini_set('display_errors', 1);
            $memoryLimit = trim(ini_get('memory_limit'));
            if ($memoryLimit != -1 && $this->getMemoryInBytes($memoryLimit) < 768 * 1024 * 1024) {
                @ini_set('memory_limit', '768M');
            }
        }
    }

    /**
     * @param string $value
     * @return int
     * @since 2.1.0
     */
    private function getMemoryInBytes($value)
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
