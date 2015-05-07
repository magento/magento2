<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/customer_review.php';

$ratingModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Review\Model\Rating',
    ['data' => [
        'rating_code' => 'test',
        'rating_codes' => ['test'],
        'position' => 0,
        'isActive' => 1,
        'entityId' => $review->getEntityId()
    ]]
);
$ratingModel->save();
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry')->register(
    'rating_data',
    $ratingModel
);
