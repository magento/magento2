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
 * Add Chile States
 */
class AddDataForChile implements DataPatchInterface
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
     * AddDataForChile constructor.
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
            $this->getDataForChile()
        );

        return $this;
    }

    /**
     * Chile states data.
     *
     * @return array
     */
    private function getDataForChile()
    {
        return [
            ['CL', 'CL-AI', 'Aisén del General Carlos Ibañez del Campo'],
            ['CL', 'CL-AN', 'Antofagasta'],
            ['CL', 'CL-AP', 'Arica y Parinacota'],
            ['CL', 'CL-AR', 'La Araucanía'],
            ['CL', 'CL-AT', 'Atacama'],
            ['CL', 'CL-BI', 'Biobío'],
            ['CL', 'CL-CO', 'Coquimbo'],
            ['CL', 'CL-LI', 'Libertador General Bernardo O\'Higgins'],
            ['CL', 'CL-LL', 'Los Lagos'],
            ['CL', 'CL-LR', 'Los Ríos'],
            ['CL', 'CL-MA', 'Magallanes'],
            ['CL', 'CL-ML', 'Maule'],
            ['CL', 'CL-NB', 'Ñuble'],
            ['CL', 'CL-RM', 'Región Metropolitana de Santiago'],
            ['CL', 'CL-TA', 'Tarapacá'],
            ['CL', 'CL-VS', 'Valparaíso'],
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
