<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddCountriesCaribbeanCuracaoKosovoSintMaarten
 *
 * @package Magento\Directory\Setup\Patch
 */
class AddCountriesCaribbeanCuracaoKosovoSintMaarten implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * AddCountriesCaribbeanCuracaoKosovoSintMaarten constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /**
         * Fill table directory/country
         */
        $data = [
            [
                'country_id' => 'BQ',
                'iso2_code' => 'BQ',
                'iso3_code' => 'BES',
            ],
            [
                'country_id' => 'CW',
                'iso2_code' => 'CW',
                'iso3_code' => 'CUW',
            ],
            [
                'country_id' => 'SX',
                'iso2_code' => 'SX',
                'iso3_code' => 'SXM',
            ],
            [
                'country_id' => 'XK',
                'iso2_code' => 'XK',
                'iso3_code' => 'XKX',
            ],
        ];

        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('directory_country'),
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            InitializeDirectoryData::class
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
