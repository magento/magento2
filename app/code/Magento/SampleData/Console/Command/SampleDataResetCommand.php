<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SampleData\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Module\PackageInfo;
use Magento\SampleData\Model\Dependency;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for reset Sample Data modules version
 */
class SampleDataResetCommand extends Command
{
    /**
     * @param Dependency $sampleDataDependency
     * @param ModuleResource $moduleResource
     * @param PackageInfo $packageInfo
     */
    public function __construct(
        private readonly Dependency $sampleDataDependency,
        private readonly ModuleResource $moduleResource,
        private readonly PackageInfo $packageInfo
    ) {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('sampledata:reset')
            ->setDescription('Reset all sample data modules for re-installation');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sampleDataPackages = $this->sampleDataDependency->getSampleDataPackages();
        if (!empty($sampleDataPackages)) {
            foreach (array_keys($sampleDataPackages) as $name) {
                $moduleName = $this->packageInfo->getModuleName($name);
                if ($moduleName !== null) {
                    $this->moduleResource->setDataVersion($moduleName, '');
                }
            }
            $output->writeln('<info>' . 'Reset of sample data version completed successfully.' . '</info>');
        } else {
            $output->writeln('<info>' . 'There is no sample data for current set of modules.' . '</info>');
        }

        return Cli::RETURN_SUCCESS;
    }
}
