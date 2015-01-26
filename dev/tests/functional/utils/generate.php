<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__FILE__) . '/' . 'bootstrap.php';

$objectManager->create('Magento\Mtf\Util\Generate\TestCase')->launch();
$objectManager->create('Magento\Mtf\Util\Generate\Page')->launch();
$objectManager->create('Magento\Mtf\Util\Generate\Fixture')->launch();
$objectManager->create('Magento\Mtf\Util\Generate\Constraint')->launch();
$objectManager->create('Magento\Mtf\Util\Generate\Handler')->launch();

$objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
$objectManager->create('Magento\Mtf\Util\Generate\Repository')->launch();

\Magento\Mtf\Util\Generate\GenerateResult::displayResults();
