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
 * Add Sweden States
 */
class AddDataForSweden implements DataPatchInterface
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
     * AddDataForSweden constructor.
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
            $this->getDataForSweden()
        );

        return $this;
    }

    /**
     * Swedish states data.
     *
     * @return array
     */
    private function getDataForSweden()
    {
        return [
            ['SE', 'SE-K', 'Blekinge län'],
            ['SE', 'SE-W', 'Dalarnas län'],
            ['SE', 'SE-I', 'Gotlands län'],
            ['SE', 'SE-X', 'Gävleborgs län'],
            ['SE', 'SE-N', 'Hallands län'],
            ['SE', 'SE-Z', 'Jämtlands län'],
            ['SE', 'SE-F', 'Jönköpings län'],
            ['SE', 'SE-H', 'Kalmar län'],
            ['SE', 'SE-G', 'Kronobergs län'],
            ['SE', 'SE-BD', 'Norrbottens län'],
            ['SE', 'SE-M', 'Skåne län'],
            ['SE', 'SE-AB', 'Stockholms län'],
            ['SE', 'SE-D', 'Södermanlands län'],
            ['SE', 'SE-C', 'Uppsala län'],
            ['SE', 'SE-S', 'Värmlands län'],
            ['SE', 'SE-AC', 'Västerbottens län'],
            ['SE', 'SE-Y', 'Västernorrlands län'],
            ['SE', 'SE-U', 'Västmanlands län'],
            ['SE', 'SE-O', 'Västra Götalands län'],
            ['SE', 'SE-T', 'Örebro län'],
            ['SE', 'SE-E', 'Östergötlands län']
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
