<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                    'rating' => mt_rand(1, 5)
                ]
            ],
            'entity_id' => ['dataSet' => 'catalogProductSimple::default']
        ];

        $this->_data['frontend_review'] = [
            'status_id' => 'Pending',
            'select_stores' => ['Main Website/Main Website Store/Default Store View'],
            'nickname' => 'nickname_%isolation%',
            'title' => 'title_%isolation%',
            'detail' => 'review_detail_%isolation%',
            'entity_id' => ['dataSet' => 'catalogProductSimple::default']
        ];
    }
}
