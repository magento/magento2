<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/** @var $integration \Magento\Integration\Model\Integration */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$integration = $objectManager->create(\Magento\Integration\Model\Integration::class);
$integration->load('Fixture Integration', 'name');
if ($integration->getId()) {
    $integration->delete();
}
