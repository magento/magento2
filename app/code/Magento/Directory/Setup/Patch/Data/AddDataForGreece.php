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
 * Add Greece States
 */
class AddDataForGreece implements DataPatchInterface
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
     * AddDataForGreece constructor.
     *
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
            $this->getDataForGreece()
        );

        return $this;
    }

    /**
     * Greece states data.
     *
     * @return array
     */
    private function getDataForGreece()
    {
        return [
            ['GR', 'GR-A', 'Anatolikí Makedonía kai Thráki'],
            ['GR', 'GR-I', 'Attikí'],
            ['GR', 'GR-G', 'Dytikí Elláda'],
            ['GR', 'GR-C', 'Dytikí Makedonía'],
            ['GR', 'GR-F', 'Ionía Nísia'],
            ['GR', 'GR-D', 'Ípeiros'],
            ['GR', 'GR-B', 'Kentrikí Makedonía'],
            ['GR', 'GR-M', 'Kríti'],
            ['GR', 'GR-L', 'Nótio Aigaío'],
            ['GR', 'GR-J', 'Pelopónnisos'],
            ['GR', 'GR-H', 'Stereá Elláda'],
            ['GR', 'GR-E', 'Thessalía'],
            ['GR', 'GR-K', 'Vóreio Aigaío'],
            ['GR', 'GR-69', 'Ágion Óros']

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
