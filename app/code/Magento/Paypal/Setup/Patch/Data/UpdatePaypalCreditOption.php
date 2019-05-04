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
 * Class AddPaypalOrderStates
 */
class UpdatePaypalCreditOption implements DataPatchInterface, PatchVersionInterface
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
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()
            ->from($this->moduleDataSetup->getTable('core_config_data'), ['scope', 'scope_id', 'value'])
            ->where('path = ?', 'payment/paypal_express_bml/active');
        foreach ($connection->fetchAll($select) as $pair) {
            if (!$pair['value']) {
                $this->moduleDataSetup->getConnection()
                    ->insertOnDuplicate(
                        $this->moduleDataSetup->getTable('core_config_data'),
                        [
                            'scope' => $pair['scope'],
                            'scope_id' => $pair['scope_id'],
                            'path' => 'paypal/style/disable_funding_options',
                            'value' => 'CREDIT'
                        ]
                    );
            }
        }
        $this->moduleDataSetup->getConnection()->endSetup();
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
