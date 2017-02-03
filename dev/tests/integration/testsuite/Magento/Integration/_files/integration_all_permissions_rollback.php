<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $integration \Magento\Integration\Model\Integration */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$integration = $objectManager->create('Magento\Integration\Model\Integration');
$integration->load('Fixture Integration', 'name');
if ($integration->getId()) {
    $integration->delete();
}
