<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__FILE__) . '/' . 'bootstrap.php';

$objectManager->create('Magento\Mtf\Util\Generate\Page')->launch();
$objectManager->create('Magento\Mtf\Util\Generate\Fixture')->launch();
$objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
$objectManager->create('Magento\Mtf\Util\Generate\Repository')->launch();
$objectManager->create('Magento\Mtf\Util\Generate\Factory')->launch();

\Magento\Mtf\Util\Generate\GenerateResult::displayResults();
