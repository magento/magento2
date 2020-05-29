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
 * Add Bulgaria States
 */
class AddDataForBulgaria implements DataPatchInterface
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
     * AddDataForBulgaria constructor.
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
            $this->getDataForBulgaria()
        );

        return $this;
    }

    /**
     * Bulgarian states data.
     *
     * @return array
     */
    private function getDataForBulgaria()
    {
        return [
            ['BG', 'BG-01', 'Blagoevgrad'],
            ['BG', 'BG-02', 'Burgas'],
            ['BG', 'BG-03', 'Varna'],
            ['BG', 'BG-04', 'Veliko Tarnovo'],
            ['BG', 'BG-05', 'Vidin'],
            ['BG', 'BG-06', 'Vratsa'],
            ['BG', 'BG-07', 'Gabrovo'],
            ['BG', 'BG-08', 'Dobrich'],
            ['BG', 'BG-09', 'Kardzhali'],
            ['BG', 'BG-10', 'Kyustendil'],
            ['BG', 'BG-11', 'Lovech'],
            ['BG', 'BG-12', 'Montana'],
            ['BG', 'BG-13', 'Pazardzhik'],
            ['BG', 'BG-14', 'Pernik'],
            ['BG', 'BG-15', 'Pleven'],
            ['BG', 'BG-16', 'Plovdiv'],
            ['BG', 'BG-17', 'Razgrad'],
            ['BG', 'BG-18', 'Ruse'],
            ['BG', 'BG-19', 'Silistra'],
            ['BG', 'BG-20', 'Sliven'],
            ['BG', 'BG-21', 'Smolyan'],
            ['BG', 'BG-22', 'Sofia City'],
            ['BG', 'BG-23', 'Sofia Province'],
            ['BG', 'BG-24', 'Stara Zagora'],
            ['BG', 'BG-25', 'Targovishte'],
            ['BG', 'BG-26', 'Haskovo'],
            ['BG', 'BG-27', 'Shumen'],
            ['BG', 'BG-28', 'Yambol'],
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
