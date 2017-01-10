<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$magentoObjectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
$objectManager->create(\Magento\Mtf\Util\Generate\Repository::class)->launch();
