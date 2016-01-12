<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $synonymsModel \Magento\Search\Model\SynonymReader */
$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('queen,monarch')->setStoreId(1)->save();

$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('british,english')->setStoreId(1)->save();

$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('schlicht,natürlich')->setStoreId(1)->save();

$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('universe,cosmos')->setStoreId(0)->save();

$synonymsModel = $objectManager->create('Magento\Search\Model\SynonymReader');
$synonymsModel->setSynonyms('big,huge,large,enormous')->setStoreId(0)->save();
