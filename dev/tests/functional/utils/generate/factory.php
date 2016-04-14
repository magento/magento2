<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$magentoObjectManager->create('Magento\Mtf\Util\Generate\Factory')->launch();
\Magento\Mtf\Util\Generate\GenerateResult::displayResults();
