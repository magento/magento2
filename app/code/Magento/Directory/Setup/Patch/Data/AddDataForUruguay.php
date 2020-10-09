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
 * Add Uruguay States/Regions
 */
class AddDataForUruguay implements DataPatchInterface
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
            $this->getDataForUruguay()
        );

        return $this;
    }

    /**
     * Uruguay states data.
     *
     * @return array
     */
    private function getDataForUruguay(): array
    {
        return [
            ['UY', 'UY-AR', 'Artigas'],
            ['UY', 'UY-CA', 'Canelones'],
            ['UY', 'UY-CL', 'Cerro Largo'],
            ['UY', 'UY-CO', 'Colonia'],
            ['UY', 'UY-DU', 'Durazno'],
            ['UY', 'UY-FS', 'Flores'],
            ['UY', 'UY-FD', 'Florida'],
            ['UY', 'UY-LA', 'Lavalleja'],
            ['UY', 'UY-MA', 'Maldonado'],
            ['UY', 'UY-MO', 'Montevideo'],
            ['UY', 'UY-PA', 'Paysandu'],
            ['UY', 'UY-RN', 'Río Negro'],
            ['UY', 'UY-RV', 'Rivera'],
            ['UY', 'UY-RO', 'Rocha'],
            ['UY', 'UY-SA', 'Salto'],
            ['UY', 'UY-SJ', 'San José'],
            ['UY', 'UY-SO', 'Soriano'],
            ['UY', 'UY-TA', 'Tacuarembó'],
            ['UY', 'UY-TT', 'Treinta y Tres']
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
