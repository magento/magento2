<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Repository;

use Magento\Mtf\Repository\AbstractRepository;

/**
 * Class Review
 * Data for creation product Review
 */
class Review extends AbstractRepository
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
                    'dataset' => 'visibleOnDefaultWebsite',
                    'rating' => mt_rand(1, 5)
                ]
            ],
            'entity_id' => ['dataset' => 'catalogProductSimple::default']
        ];

        $this->_data['frontend_review'] = [
            'status_id' => 'Pending',
            'select_stores' => ['Main Website/Main Website Store/Default Store View'],
            'nickname' => 'nickname_%isolation%',
            'title' => 'title_%isolation%',
            'detail' => 'review_detail_%isolation%',
            'entity_id' => ['dataset' => 'catalogProductSimple::default']
        ];
    }
}
