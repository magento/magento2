<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Update existing customization for the smart button size value to be compatible with the new PayPal SDK
 */
class UpdateSmartButtonSize implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var array
     */
    private $sizeSettingsToUpdate = [
        'paypal/style/checkout_page_button_size',
        'paypal/style/cart_page_button_size',
        'paypal/style/mini_cart_page_button_size',
        'paypal/style/checkout_page_button_size'
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
        $connection = $this->moduleDataSetup->getConnection();

        $select = $connection->select()
            ->from($this->moduleDataSetup->getTable('core_config_data'), ['path','scope', 'scope_id', 'value'])
            ->where('path IN (?)', $this->sizeSettingsToUpdate)
            ->where('value = \'large\' OR value=\'medium\'');
        foreach ($connection->fetchAll($select) as $pair) {
            $this->moduleDataSetup->getConnection()
                ->insertOnDuplicate(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    [
                        'scope' => $pair['scope'],
                        'scope_id' => $pair['scope_id'],
                        'path' => $pair['path'],
                        'value' => 'responsive'
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
    public static function getVersion()
    {
        return '2.3.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
