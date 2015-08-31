<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
use Composer\Command\RequireCommand;
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
     * @var RequireCommand
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
        $installExtensions = $this->sampleDataDependency->getSampleDataPackages();
        if (!empty($installExtensions)) {
            $baseDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
            $requireArgs = ['command' => 'require', '-n', '-d' => $baseDir, '--no-progress' => 1, '--no-update' => 1];
            foreach ($installExtensions as $name => $version) {
                $requireArgs['packages'][] = "$name:$version";
            }
            /** @var ArrayInput $commandInput */
            $commandInput = $this->arrayInputFactory->create(['parameters' => $requireArgs]);
            /** @var Application $application */
            $application = $this->applicationFactory->create();
            $application->setAutoExit(false);
            $application->run($commandInput, $output);
        } else {
            $output->writeln('<info>' . 'There is no sample data for current set of modules.' . '</info>');
        }
    }
}
