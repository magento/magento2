<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__FILE__) . '/' . 'bootstrap.php';

$objectManager->create('Mtf\Util\Generate\Page')->launch();
$objectManager->create('Mtf\Util\Generate\Fixture')->launch();
$objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
$objectManager->create('Mtf\Util\Generate\Repository')->launch();
$objectManager->create('Mtf\Util\Generate\Factory')->launch();

\Mtf\Util\Generate\GenerateResult::displayResults();
