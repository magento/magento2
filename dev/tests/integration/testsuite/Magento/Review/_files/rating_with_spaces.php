<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Review\Model\Rating;
use Magento\Review\Model\Rating\Option;
use Magento\TestFramework\Helper\Bootstrap;

$ratingData = [
    'rating_code' => 'test rating with spaces',
    'rating_codes' => [
        1 => '',
    ],
    'stores' => [
        0 => '1',
        1 => 0,
    ],
    'position' => 0,
    'is_active' => true,
    'entity_id' => '1',
];
/** @var Rating $rating */
$rating = Bootstrap::getObjectManager()->get(Rating::class);
$rating->addData($ratingData);
$rating->save();

$options = [
    'add_1' => '1',
    'add_2' => '2',
    'add_3' => '3',
    'add_4' => '4',
    'add_5' => '5',
];
$i = 1;
foreach ($options as $key => $optionCode) {
    $optionModel = Bootstrap::getObjectManager()->create(Option::class);
    $optionModel->setCode($optionCode)
        ->setValue($i)
        ->setRatingId($rating->getId())
        ->setPosition($i)
        ->save();
    $i++;
}
