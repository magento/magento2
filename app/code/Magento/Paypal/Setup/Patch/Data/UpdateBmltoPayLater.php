<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update bml config settings to the equivalent paylater settings
 */
class UpdateBmltoPayLater implements DataPatchInterface
{
    /**
     * BML config path
     */
    private const BMLPATH = 'payment/paypal_express_bml/';

    /**
     * PayLater config path
     */
    private const PAYLATERPATH = 'payment/paypal_paylater/';

    /**
     * @var array
     */
    private $bmlToPayLaterSettings = [
        [
            'pages' => ['productpage'],
            'data' => [
                'display' => [
                    'name' => 'display',
                    'values' => ['0' => '0', '1' => '1']
                ],
                'position' => [
                    'name' =>'position',
                    'values' => ['0' => 'header', '1' => 'near_pp_button'],
                    'requires' => [
                        'header' => ['name' => 'stylelayout', 'value' => 'flex'],
                        'near_pp_button' => ['name' => 'stylelayout', 'value' => 'text']
                    ]
                ],
                'size' => [
                    'name' => 'ratio',
                    'values' => [
                        '190x100' => '8x1',
                        '234x60' => '8x1',
                        '300x50' => '8x1',
                        '468x60' => '8x1',
                        '728x90' => '20x1',
                        '800x66' => '20x1'
                    ],
                    'depends' => ['name' => 'position', 'value' => '0']
                ]
            ]
        ]
    ];

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PrepareInitialConfig constructor.
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
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'payment/paypal_paylater/experience_active',
                'value' => '1'
            ]
        );

        $select = $this->moduleDataSetup->getConnection()->select()
            ->from(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['path', 'value']
            )
            ->where('path LIKE ?', self::BMLPATH . '%');
        $bmlSettings = $this->moduleDataSetup->getConnection()->fetchPairs($select);

        $enabled = false;
        foreach ($bmlSettings as $bmlPath => $bmlValue) {
            $setting = str_replace(self::BMLPATH, '', $bmlPath);
            $settingParts = explode('_', $setting);
            $page = $settingParts[0];
            $setting = $settingParts[1];
            $payLaterPath = self::PAYLATERPATH . $page;

            if ($setting === 'display' && $bmlValue === '1') {
                $enabled = true;
            }

            foreach ($this->bmlToPayLaterSettings as $bmlToPayLaterSetting) {
                if (in_array($page, $bmlToPayLaterSetting['pages'])
                    && array_key_exists($setting, $bmlToPayLaterSetting['data'])
                ) {
                    $pageSetting = $bmlToPayLaterSetting['data'][$setting];
                    $dependsPath = isset($pageSetting['depends'])
                        ? self::BMLPATH . $page . '_' . $pageSetting['depends']['name']
                        : '';

                    if (!array_key_exists('depends', $pageSetting)
                        || (array_key_exists($dependsPath, $bmlSettings)
                            && $bmlSettings[$dependsPath] === $pageSetting['depends']['value'])
                    ) {
                        $path = $payLaterPath . '_' . $pageSetting['name'];
                        $value = $pageSetting['values'][$bmlValue];
                        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                            $this->moduleDataSetup->getTable('core_config_data'),
                            [
                                'scope' => 'default',
                                'scope_id' => 0,
                                'path' => $path,
                                'value' => $value
                            ]
                        );
                        if (array_key_exists('requires', $pageSetting)
                            && array_key_exists($value, $pageSetting['requires'])
                        ) {
                            $requiredPath = $payLaterPath . '_' . $pageSetting['requires'][$value]['name'];
                            $requiredValue = $pageSetting['requires'][$value]['value'];
                            $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                                $this->moduleDataSetup->getTable('core_config_data'),
                                [
                                    'scope' => 'default',
                                    'scope_id' => 0,
                                    'path' => $requiredPath,
                                    'value' => $requiredValue
                                ]
                            );
                        }
                    }
                }
            }
        }
        if ($enabled) {
            $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                $this->moduleDataSetup->getTable('core_config_data'),
                [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => 'payment/paypal_paylater/enabled',
                    'value' => '1'
                ]
            );
        }
        return $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
