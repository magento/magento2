<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

$magentoObjectManager->create(
    'Magento\Mtf\Util\Generate\Fixture\SchemaXml',
    ['objectManager' => $magentoObjectManager]
)->launch();
