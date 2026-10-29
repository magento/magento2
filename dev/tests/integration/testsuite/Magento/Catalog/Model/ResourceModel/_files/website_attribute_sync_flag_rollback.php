<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\FlagManager;
use \Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;

/**
 * @var FlagManager $flagManager
 */
$flagManager = ObjectManager::getInstance()->get(FlagManager::class);
$flagManager->deleteFlag(WebsiteAttributesSynchronizer::FLAG_NAME);
