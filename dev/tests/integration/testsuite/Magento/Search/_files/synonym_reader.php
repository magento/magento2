<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

// Synonym groups for "Default Store View"
/** @var $synonymsModel \Magento\Search\Model\SynonymReader */
$synonymsModel = $objectManager->create(\Magento\Search\Model\SynonymReader::class);
$synonymsModel->setSynonyms('queen,monarch')->setStoreId(1)->save();

$synonymsModel = $objectManager->create(\Magento\Search\Model\SynonymReader::class);
$synonymsModel->setSynonyms('british,english')->setStoreId(1)->save();

$synonymsModel = $objectManager->create(\Magento\Search\Model\SynonymReader::class);
$synonymsModel->setSynonyms('schlicht,natürlich')->setStoreId(1)->save();

// Synonym groups for "Main Website"
$synonymsModel = $objectManager->create(\Magento\Search\Model\SynonymReader::class);
$synonymsModel->setSynonyms('orange,magento')->setWebsiteId(1)->save();

$synonymsModel = $objectManager->create(\Magento\Search\Model\SynonymReader::class);
$synonymsModel->setSynonyms('hill,mountain,peak')->setWebsiteId(1)->save();

// Synonym groups for "All Store Views"
$synonymsModel = $objectManager->create(\Magento\Search\Model\SynonymReader::class);
$synonymsModel->setSynonyms('universe,cosmos')->setWebsiteId(0)->save();

$synonymsModel = $objectManager->create(\Magento\Search\Model\SynonymReader::class);
$synonymsModel->setSynonyms('big,huge,large,enormous')->setWebsiteId(0)->save();
