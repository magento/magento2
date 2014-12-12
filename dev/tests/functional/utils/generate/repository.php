<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
$objectManager->create('Mtf\Util\Generate\Repository')->launch();
\Mtf\Util\Generate\GenerateResult::displayResults();
