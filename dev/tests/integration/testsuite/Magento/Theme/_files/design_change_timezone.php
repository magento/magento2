<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$designChanges = [
    ['store' => 'default', 'design' => 'default_yesterday_design', 'date' => '-1 day'],
    ['store' => 'default', 'design' => 'default_today_design', 'date' => 'now'],
    ['store' => 'default', 'design' => 'default_tomorrow_design', 'date' => '+1 day'],
    ['store' => 'admin', 'design' => 'admin_yesterday_design', 'date' => '-1 day'],
    ['store' => 'admin', 'design' => 'admin_today_design', 'date' => 'now'],
    ['store' => 'admin', 'design' => 'admin_tomorrow_design', 'date' => '+1 day'],
];
foreach ($designChanges as $designChangeData) {
    $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getStore(
        $designChangeData['store']
    )->getId();
    $change = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Theme\Model\Design::class);
    $change->setStoreId(
        $storeId
    )->setDesign(
        $designChangeData['design']
    )->setDateFrom(
        $designChangeData['date']
    )->setDateTo(
        $designChangeData['date']
    )->save();
}
