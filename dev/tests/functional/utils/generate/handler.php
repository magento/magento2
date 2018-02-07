<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$objectManager->create('Magento\Mtf\Util\Generate\Handler')->launch();
\Magento\Mtf\Util\Generate\GenerateResult::displayResults();
