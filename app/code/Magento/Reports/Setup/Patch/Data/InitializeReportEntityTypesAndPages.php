<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Setup\Patch\Data;

use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class InitializeReportEntityTypesAndPages
 * @package Magento\Reports\Setup\Patch
 */
class InitializeReportEntityTypesAndPages implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * InitializeReportEntityTypesAndPages constructor.
     * @param ResourceConnection $resourceConnection
     * @param PageFactory $pageFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->pageFactory = $pageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->startSetup();
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
            $this->resourceConnection->getConnection()
                ->insertForce($this->resourceConnection->getConnection()->getTableName('report_event_types'), $row);
        }
        /**
         * Prepare database after data upgrade
         */
        $this->resourceConnection->getConnection()->endSetup();
        /**
         * Cms Page  with 'home' identifier page modification for report pages
         */
        /** @var $cms \Magento\Cms\Model\Page */
        $cms = $this->pageFactory->create();
        $cms->load('home', 'identifier');
        // @codingStandardsIgnoreStart
        $reportLayoutUpdate = '<!--
    <referenceContainer name="right">
        <referenceBlock name="catalog.compare.sidebar" remove="true" />
    </referenceContainer>-->';
        // @codingStandardsIgnoreEnd
        /*
         * Merge and save old layout update data with report layout data
         */
        $cms->setLayoutUpdateXml($cms->getLayoutUpdateXml() . $reportLayoutUpdate)
            ->save();
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
