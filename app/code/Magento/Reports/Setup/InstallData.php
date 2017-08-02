<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Setup;

use Magento\Cms\Model\PageFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * Page factory
     *
     * @var PageFactory
     * @since 2.0.0
     */
    private $pageFactory;

    /**
     * Init
     *
     * @param PageFactory $pageFactory
     * @since 2.0.0
     */
    public function __construct(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
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
            $setup->getConnection()
                ->insertForce($setup->getTable('report_event_types'), $row);
        }

        /**
         * Prepare database after data upgrade
         */
        $setup->endSetup();

        /**
         * Cms Page  with 'home' identifier page modification for report pages
         */
        /** @var $cms \Magento\Cms\Model\Page */
        $cms = $this->pageFactory->create();
        $cms->load('home', 'identifier');

        // @codingStandardsIgnoreStart
        $reportLayoutUpdate = '<!--
    <referenceContainer name="right">
        <action method="unsetChild"><argument name="alias" xsi:type="string">right.reports.product.viewed</argument></action>
        <action method="unsetChild"><argument name="alias" xsi:type="string">right.reports.product.compared</argument></action>
    </referenceContainer>-->';
        // @codingStandardsIgnoreEnd

        /*
         * Merge and save old layout update data with report layout data
         */
        $cms->setLayoutUpdateXml($cms->getLayoutUpdateXml() . $reportLayoutUpdate)
            ->save();
    }
}
