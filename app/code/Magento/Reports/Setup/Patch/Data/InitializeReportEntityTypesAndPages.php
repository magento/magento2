<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Reports\Model\Event;

class InitializeReportEntityTypesAndPages implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /*
         * Report Event Types default data
         */
        $eventTypeData = [
            [
                'event_type_id' => Event::EVENT_PRODUCT_VIEW,
                'event_name' => 'catalog_product_view',
            ],
            [
                'event_type_id' => Event::EVENT_PRODUCT_SEND,
                'event_name' => 'sendfriend_product',
            ],
            [
                'event_type_id' => Event::EVENT_PRODUCT_COMPARE,
                'event_name' => 'catalog_product_compare_add_product',
            ],
            [
                'event_type_id' => Event::EVENT_PRODUCT_TO_CART,
                'event_name' => 'checkout_cart_add_product',
            ],
            [
                'event_type_id' => Event::EVENT_PRODUCT_TO_WISHLIST,
                'event_name' => 'wishlist_add_product',
            ],
            [
                'event_type_id' => Event::EVENT_WISHLIST_SHARE,
                'event_name' => 'wishlist_share',
            ],
        ];

        foreach ($eventTypeData as $row) {
            $this->moduleDataSetup->getConnection()
                ->insertForce($this->moduleDataSetup->getTable('report_event_types'), $row);
        }

        /**
         * Prepare database after data upgrade
         */
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
