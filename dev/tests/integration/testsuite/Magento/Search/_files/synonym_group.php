<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Search\Model\SynonymGroupRepository;
use Magento\Search\Api\Data\SynonymGroupInterface;

$objectManager = Bootstrap::getObjectManager();

$synonymsGroupModel = $objectManager->create(SynonymGroupInterface::class);
$synonymGroupRepository=$objectManager->create(SynonymGroupRepository::class);
$synonymsGroupModel->setStoreId(Magento\Store\Model\Store::DEFAULT_STORE_ID)->setStoreId(0)->setWebsiteId(0);

$synonymGroupRepository->save($synonymsGroupModel);
