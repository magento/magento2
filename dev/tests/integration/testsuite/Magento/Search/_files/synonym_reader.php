<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

// Synonym groups for "Default Store View"
/** @var $synonymsModel \Magento\Search\Model\SynonymReader */
$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('queen,monarch')->setScopeId(1)->setScopeType('stores')->save();

$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('british,english')->setScopeId(1)->setScopeType('stores')->save();

$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('schlicht,natürlich')->setScopeId(1)->setScopeType('stores')->save();

// Synonym groups for "Main Website"
$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('orange,magento')->setScopeId(1)->setScopeType('websites')->save();

$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('hill,mountain,peak')->setScopeId(1)->setScopeType('websites')->save();

// Synonym groups for "All Store Views"
$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('universe,cosmos')->setScopeId(0)->setScopeType('default')->save();

$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('big,huge,large,enormous')->setScopeId(0)->setScopeType('default')->save();

