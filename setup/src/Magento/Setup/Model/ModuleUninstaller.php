<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Setup\Patch\PatchApplier;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class to uninstall a module component
 */
class ModuleUninstaller
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Composer\Remove
     */
    private $remove;

    /**
     * @var UninstallCollector
     */
    private $collector;

    /**
     * @var \Magento\Setup\Module\SetupFactory
     */
    private $setupFactory;
    /**
     * @var PatchApplier
     */
    private $patchApplier;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param \Magento\Framework\Composer\Remove $remove
     * @param UninstallCollector $collector
     * @param \Magento\Setup\Module\SetupFactory $setupFactory
     * @param PatchApplier $patchApplier
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
     * @return PatchApplier
     */
    private function getPatchApplier()
    {
        if (!$this->patchApplier) {
            $this->patchApplier = $this->objectManager->get(PatchApplier::class);
        }

        return $this->patchApplier;
    }

    /**
     * Invoke remove data routine in each specified module
     *
     * @param OutputInterface $output
     * @param array $modules
     * @return void
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
            }

            $this->getPatchApplier()->revertDataPatches($module);
        }
    }

    /**
     * Run 'composer remove' to remove code
     *
     * @param OutputInterface $output
     * @param array $modules
     * @return void
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
