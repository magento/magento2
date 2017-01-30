<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SampleData\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\SampleData\Model\Dependency;
use Magento\Framework\App\State;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\ArrayInputFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Composer\Console\Application;
use Composer\Console\ApplicationFactory;

/**
 * Command for deployment of Sample Data
 */
class SampleDataDeployCommand extends Command
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Dependency
     */
    private $sampleDataDependency;

    /**
     * @var ArrayInputFactory
     */
    private $arrayInputFactory;

    /**
     * @var ApplicationFactory
     */
    private $applicationFactory;

    /**
     * @param Filesystem $filesystem
     * @param Dependency $sampleDataDependency
     * @param ArrayInputFactory $arrayInputFactory
     * @param ApplicationFactory $applicationFactory
     */
    public function __construct(
        Filesystem $filesystem,
        Dependency $sampleDataDependency,
        ArrayInputFactory $arrayInputFactory,
        ApplicationFactory $applicationFactory
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
            /** @var ArrayInput $commandInput */
            $commandInput = $this->arrayInputFactory->create(['parameters' => $arguments]);

            /** @var Application $application */
            $application = $this->applicationFactory->create();
            $application->setAutoExit(false);
            $result = $application->run($commandInput, $output);
            if ($result !== 0) {
                $output->writeln('<info>' . 'There is an error during sample data deployment.' . '</info>');
            }
        } else {
            $output->writeln('<info>' . 'There is no sample data for current set of modules.' . '</info>');
        }
    }
}
