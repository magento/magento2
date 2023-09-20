<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Directory\Setup\DataInstallerFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add Czech Republic States
 */
class AddDataForCzechia implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var DataInstallerFactory
     */
    private $dataInstallerFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param DataInstallerFactory $dataInstallerFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        DataInstallerFactory $dataInstallerFactory
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
            $this->getDataForCzechia()
        );
        
        return $this;
    }

    /**
     * Czechia states data.
     *
     * @return array
     */
    private function getDataForCzechia()
    {
        return [
            ['CZ', 'CZ-10', 'Praha, Hlavní město'],
            ['CZ', 'CZ-20', 'Středočeský kraj'],
            ['CZ', 'CZ-31', 'Jihočeský kraj'],
            ['CZ', 'CZ-32', 'Plzeňský kraj'],
            ['CZ', 'CZ-41', 'Karlovarský kraj'],
            ['CZ', 'CZ-42', 'Ústecký kraj'],
            ['CZ', 'CZ-51', 'Liberecký kraj'],
            ['CZ', 'CZ-52', 'Královéhradecký kraj'],
            ['CZ', 'CZ-53', 'Pardubický kraj'],
            ['CZ', 'CZ-63', 'Kraj Vysočina'],
            ['CZ', 'CZ-64', 'Jihomoravský kraj'],
            ['CZ', 'CZ-71', 'Olomoucký kraj'],
            ['CZ', 'CZ-72', 'Zlínský kraj'],
            ['CZ', 'CZ-80', 'Moravskoslezský kraj'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            InitializeDirectoryData::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
