<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
require_once dirname(__FILE__) . '/' . 'bootstrap.php';

$objectManager->create('Mtf\Util\Generate\TestCase')->launch();
$objectManager->create('Mtf\Util\Generate\Page')->launch();
$objectManager->create('Mtf\Util\Generate\Fixture')->launch();
$objectManager->create('Mtf\Util\Generate\Constraint')->launch();
$objectManager->create('Mtf\Util\Generate\Handler')->launch();

$objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
$objectManager->create('Mtf\Util\Generate\Repository')->launch();

\Mtf\Util\Generate\GenerateResult::displayResults();
