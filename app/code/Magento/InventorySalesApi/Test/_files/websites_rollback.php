<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

for ($i = 0; $i < 3; $i++) {
    /** @var Website $website */
    $website = Bootstrap::getObjectManager()->create(Website::class);
    $website->load('test_' . $i);
    $website->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
