<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class to uninstall a module component
 * @since 2.0.0
 */
class ModuleUninstaller
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Composer\Remove
     * @since 2.0.0
     */
    private $remove;

    /**
     * @var UninstallCollector
     * @since 2.0.0
     */
    private $collector;

    /**
     * @var \Magento\Setup\Module\SetupFactory
     * @since 2.0.0
     */
    private $setupFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param \Magento\Framework\Composer\Remove $remove
     * @param UninstallCollector $collector
     * @param \Magento\Setup\Module\SetupFactory $setupFactory
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        \Magento\Framework\Composer\Remove $remove,
        UninstallCollector $collector,
        \Magento\Setup\Module\SetupFactory $setupFactory
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->remove = $remove;
        $this->collector = $collector;
        $this->setupFactory = $setupFactory;
    }

    /**
     * Invoke remove data routine in each specified module
     *
     * @param OutputInterface $output
     * @param array $modules
     * @return void
     * @since 2.0.0
     */
    public function uninstallData(OutputInterface $output, array $modules)
    {
        $uninstalls = $this->collector->collectUninstall($modules);
        $setupModel = $this->setupFactory->create();
        $resource = $this->objectManager->get(\Magento\Framework\Module\ModuleResource::class);
        foreach ($modules as $module) {
            if (isset($uninstalls[$module])) {
                $output->writeln("<info>Removing data of $module</info>");
                $uninstalls[$module]->uninstall(
                    $setupModel,
                    new ModuleContext($resource->getDbVersion($module) ?: '')
                );
            } else {
                $output->writeln("<info>No data to clear in $module</info>");
            }
        }
    }

    /**
     * Run 'composer remove' to remove code
     *
     * @param OutputInterface $output
     * @param array $modules
     * @return void
     * @since 2.0.0
     */
    public function uninstallCode(OutputInterface $output, array $modules)
    {
        $output->writeln('<info>Removing code from Magento codebase:</info>');
        $packages = [];
        /** @var \Magento\Framework\Module\PackageInfo $packageInfo */
        $packageInfo = $this->objectManager->get(\Magento\Framework\Module\PackageInfoFactory::class)->create();
        foreach ($modules as $module) {
            $packages[] = $packageInfo->getPackageName($module);
        }
        $this->remove->remove($packages);
    }
}
