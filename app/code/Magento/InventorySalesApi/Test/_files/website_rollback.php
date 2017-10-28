<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

for ($i = 0; $i < 3; $i++) {
    /** @var \Magento\Store\Model\Website $website */
    $website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Website::class);
    $website->load('test-' . $i);
    $website->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
