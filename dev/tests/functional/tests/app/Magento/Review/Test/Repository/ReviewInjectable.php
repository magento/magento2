<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class ReviewInjectable
 * Data for creation product Review
 */
class ReviewInjectable extends AbstractRepository
{
    /**
     * Constructor
     *
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['review_for_simple_product_with_rating'] = [
            'status_id' => 'Approved',
            'select_stores' => ['Main Website/Main Website Store/Default Store View'],
            'nickname' => 'nickname_%isolation%',
            'title' => 'title_%isolation%',
            'detail' => 'review_detail_%isolation%',
            'ratings' => [
                [
                    'dataSet' => 'visibleOnDefaultWebsite',
                    'rating' => mt_rand(1, 5),
                ],
            ],
            'entity_id' => ['dataSet' => 'catalogProductSimple::default'],
        ];

        $this->_data['frontend_review'] = [
            'status_id' => 'Pending',
            'select_stores' => ['Main Website/Main Website Store/Default Store View'],
            'nickname' => 'nickname_%isolation%',
            'title' => 'title_%isolation%',
            'detail' => 'review_detail_%isolation%',
            'entity_id' => ['dataSet' => 'catalogProductSimple::default'],
        ];
    }
}
