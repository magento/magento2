<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

$magentoObjectManager->create(
    \Magento\Mtf\Util\Generate\Fixture\SchemaXml::class,
    ['objectManager' => $magentoObjectManager]
)->launch();
