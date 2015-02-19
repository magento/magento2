<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$fieldsProvider = $magentoObjectManager->create('\Magento\Mtf\Util\Generate\Fixture\FieldsProvider');
$objectManager->create('Magento\Mtf\Util\Generate\Fixture', ['fieldsProvider' => $fieldsProvider])->launch();
