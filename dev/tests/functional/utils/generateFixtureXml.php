<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

$magentoObjectManager->create(
    'Magento\Mtf\Util\Generate\Fixture\SchemaXml',
    ['objectManager' => $magentoObjectManager]
)->launch();
