<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class InitializeReportEntityTypesAndPages
 * @package Magento\Reports\Setup\Patch
 */
class InitializeReportEntityTypesAndPages implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * InitializeReportEntityTypesAndPages constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /*
         * Report Event Types default data
         */
        $eventTypeData = [
            [
                'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW,
                'event_name' => 'catalog_product_view'
            ],
            ['event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_SEND, 'event_name' => 'sendfriend_product'],
            [
                'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_COMPARE,
                'event_name' => 'catalog_product_compare_add_product'
            ],
            [
                'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_TO_CART,
                'event_name' => 'checkout_cart_add_product'
            ],
            [
                'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_TO_WISHLIST,
                'event_name' => 'wishlist_add_product'
            ],
            ['event_type_id' => \Magento\Reports\Model\Event::EVENT_WISHLIST_SHARE, 'event_name' => 'wishlist_share'],
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
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
