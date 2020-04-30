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
 * Update existing customization for the smart button label to be compatible with the new PayPal SDK
 */
class UpdateSmartButtonLabel implements DataPatchInterface
{
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
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['value' => 'paypal'],
            [
                'path IN (?)' => [
                    'paypal/style/checkout_page_button_label',
                    'paypal/style/cart_page_button_label',
                    'paypal/style/mini_cart_page_button_label'
                ],
                'value = ? ' => 'credit'
            ]
        );
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['value' => 'buynow'],
            [
                'path IN (?)' => ['paypal/style/product_page_button_label'],
                'value = ? ' => 'credit'
            ]
        );
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
