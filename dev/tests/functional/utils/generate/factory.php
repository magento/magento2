<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$magentoObjectManager->create('Magento\Mtf\Util\Generate\Factory')->launch();
\Magento\Mtf\Util\Generate\GenerateResult::displayResults();
