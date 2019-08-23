<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require_once __DIR__ . '/website.php';

/**
 * @var \Magento\Store\Model\Group $storeGroup
 */
$storeGroup = $objectManager->create(\Magento\Store\Model\Group::class);
$storeGroup->setCode('some_group')
    ->setName('custom store group')
    ->setWebsite($website);
$storeGroup->save($storeGroup);

/* Refresh stores memory cache */
$objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();
