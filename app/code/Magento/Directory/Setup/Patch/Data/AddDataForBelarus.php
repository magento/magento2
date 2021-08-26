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
 * Add Regions for Belarus.
 */
class AddDataForBelarus implements DataPatchInterface
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
            $this->getDataForBelarus()
        );
    }

    /**
     * Belarus regions data.
     *
     * @return array
     */
    private function getDataForBelarus(): array
    {
        return [
            ['BY', 'BY-BR', 'Bresckaja voblasć'],
            ['BY', 'BY-HO', 'Homieĺskaja voblasć'],
            ['BY', 'BY-HM', 'Horad Minsk'],
            ['BY', 'BY-HR', 'Hrodzienskaja voblasć'],
            ['BY', 'BY-MA', 'Mahilioŭskaja voblasć'],
            ['BY', 'BY-MI', 'Minskaja voblasć'],
            ['BY', 'BY-VI', 'Viciebskaja voblasć'],
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
