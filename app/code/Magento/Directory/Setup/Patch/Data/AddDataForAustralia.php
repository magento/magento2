<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Adds Australian States
 */
class AddDataForAustralia implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Directory\Setup\DataInstallerFactory
     */
    private $dataInstallerFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Directory\Setup\DataInstallerFactory $dataInstallerFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Directory\Setup\DataInstallerFactory $dataInstallerFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->dataInstallerFactory = $dataInstallerFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var DataInstaller $dataInstaller */
        $dataInstaller = $this->dataInstallerFactory->create();
        $dataInstaller->addCountryRegions(
            $this->moduleDataSetup->getConnection(),
            $this->getDataForAustralia()
        );
    }

    /**
     * Australian states data.
     *
     * @return array
     */
    private function getDataForAustralia()
    {
        return [
            ['AU', 'ACT', 'Australian Capital Territory'],
            ['AU', 'NSW', 'New South Wales'],
            ['AU', 'VIC', 'Victoria'],
            ['AU', 'QLD', 'Queensland'],
            ['AU', 'SA',  'South Australia'],
            ['AU', 'TAS', 'Tasmania'],
            ['AU', 'WA',  'Western Australia'],
            ['AU', 'NT',  'Northern Territory']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            InitializeDirectoryData::class,
            AddDataForCroatia::class,
            AddDataForIndia::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.3';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
