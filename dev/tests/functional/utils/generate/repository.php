<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$magentoObjectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
$objectManager->create('Magento\Mtf\Util\Generate\Repository')->launch();
