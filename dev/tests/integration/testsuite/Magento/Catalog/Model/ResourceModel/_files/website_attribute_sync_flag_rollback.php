<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\FlagManager;
use \Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;

/**
 * @var FlagManager $flagManager
 */
$flagManager = ObjectManager::getInstance()->get(FlagManager::class);
$flagManager->deleteFlag(WebsiteAttributesSynchronizer::FLAG_NAME);
