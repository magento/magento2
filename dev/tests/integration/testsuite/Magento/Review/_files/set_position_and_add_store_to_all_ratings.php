<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;
use Magento\Review\Model\ResourceModel\Rating as RatingResourceModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->loadArea(FrontNameResolver::AREA_CODE);

$objectManager = Bootstrap::getObjectManager();

$storeId = $objectManager->get(StoreManagerInterface::class)->getStore()->getId();

/** @var RatingResourceModel $ratingResourceModel */
$ratingResourceModel = $objectManager->create(RatingResourceModel::class);

/** @var RatingCollection $ratingCollection */
$ratingCollection = $objectManager->create(RatingCollection::class)->setOrder('rating_code', 'ASC');
$position = 0;

foreach ($ratingCollection as $rating) {
    $rating->setStores([$storeId])->setPosition($position++);
    $ratingResourceModel->save($rating);
}
