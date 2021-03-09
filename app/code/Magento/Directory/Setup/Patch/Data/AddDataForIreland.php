<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Directory\Setup\DataInstallerFactory;
use Magento\Directory\Setup\Patch\Data\InitializeDirectoryData;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add Ireland States
 */
class AddDataForIreland implements DataPatchInterface
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
            $this->getDataForIreland()
        );
    }

    /**
     * Ireland states data.
     *
     * @return array
     */
    private function getDataForIreland()
    {
        return [
            ['IE', 'CW', 'Carlow'],
            ['IE', 'CN', 'Cavan'],
            ['IE', 'CE', 'Clare'],
            ['IE', 'CO', 'Cork'],
            ['IE', 'DL', 'Donegal'],
            ['IE', 'D', 'Dublin'],
            ['IE', 'G', 'Galway'],
            ['IE', 'KY', 'Kerry'],
            ['IE', 'KE', 'Kildare'],
            ['IE', 'KK', 'Kilkenny'],
            ['IE', 'LS', 'Laois'],
            ['IE', 'LM', 'Leitrim'],
            ['IE', 'LK', 'Limerick'],
            ['IE', 'LD', 'Longford'],
            ['IE', 'LH', 'Louth'],
            ['IE', 'MO', 'Mayo'],
            ['IE', 'MH', 'Meath'],
            ['IE', 'MN', 'Monaghan'],
            ['IE', 'OY', 'Offaly'],
            ['IE', 'RN', 'Roscommon'],
            ['IE', 'SO', 'Sligo'],
            ['IE', 'TA', 'Tipperary'],
            ['IE', 'WD', 'Waterford'],
            ['IE', 'WH', 'Westmeath'],
            ['IE', 'WX', 'Wexford'],
            ['IE', 'WW', 'Wicklow'],
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
