<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SampleData\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\SampleData\Model\Dependency;

/**
 * Command for reset Sample Data modules version
 */
class SampleDataResetCommand extends Command
{
    /**
     * @var Dependency
     */
    private $sampleDataDependency;

    /**
     * @var \Magento\Framework\Module\ModuleResource
     */
    private $moduleResource;

    /**
     * @var \Magento\Framework\Module\PackageInfo
     */
    private $packageInfo;

    /**
     * @param Dependency $sampleDataDependency
     * @param \Magento\Framework\Module\ModuleResource $moduleResource
     * @param \Magento\Framework\Module\PackageInfo $packageInfo
     */
    public function __construct(
        Dependency $sampleDataDependency,
        \Magento\Framework\Module\ModuleResource $moduleResource,
        \Magento\Framework\Module\PackageInfo $packageInfo
    ) {
        $this->sampleDataDependency = $sampleDataDependency;
        $this->moduleResource = $moduleResource;
        $this->packageInfo = $packageInfo;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sampledata:reset')
            ->setDescription('Reset all sample data modules for re-installation');
        parent::configure();
    }

    /**
     * {@inheritdoc}
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
    }
}
