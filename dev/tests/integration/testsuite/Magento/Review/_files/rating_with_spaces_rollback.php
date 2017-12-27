<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Review\Model\Rating;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Rating $rating */
$rating = Bootstrap::getObjectManager()->get(Rating::class);
$rating->load('test rating with spaces', 'rating_code');
if ($rating->getId()) {
    $rating->delete();
}
