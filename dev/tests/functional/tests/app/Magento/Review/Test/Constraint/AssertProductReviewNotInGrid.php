<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductReviewNotInGrid
 * Check that Product Review not available in grid
 */
class AssertProductReviewNotInGrid extends AbstractConstraint
{
    /**
     * Filter params
     *
     * @var array
     */
    public $filter = [
        'review_id',
        'status' => 'status_id',
        'title',
        'nickname',
        'detail',
        'visible_in' => 'select_stores',
        'type',
        'name',
        'sku',
    ];

    /**
     * Asserts Product Review not available in grid
     *
     * @param ReviewIndex $reviewIndex
     * @param Review $review
     * @param string $gridStatus
     * @param Review $reviewInitial
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        Review $review,
        $gridStatus = '',
        Review $reviewInitial = null
    ) {
        $product = $reviewInitial === null
            ? $review->getDataFieldConfig('entity_id')['source']->getEntity()
            : $reviewInitial->getDataFieldConfig('entity_id')['source']->getEntity();
        $filter = $this->prepareFilter($product, $review, $gridStatus);

        $reviewIndex->getReviewGrid()->search($filter);
        unset($filter['visible_in']);
        \PHPUnit_Framework_Assert::assertFalse(
            $reviewIndex->getReviewGrid()->isRowVisible($filter, false),
            'Review available in grid'
        );
    }

    /**
     * Prepare filter for assert
     *
     * @param FixtureInterface $product
     * @param Review $review
     * @param string $gridStatus
     * @return array
     */
    public function prepareFilter(FixtureInterface $product, Review $review, $gridStatus)
    {
        $filter = [];
        foreach ($this->filter as $key => $item) {
            list($type, $param) = [$key, $item];
            if (is_numeric($key)) {
                $type = $param = $item;
            }
            switch ($param) {
                case 'name':
                case 'sku':
                    $value = $product->getData($param);
                    break;
                case 'select_stores':
                    $value = $review->getData($param)[0];
                    break;
                case 'status_id':
                    $value = $gridStatus != '' ? $gridStatus : $review->getData($param);
                    break;
                default:
                    $value = $review->getData($param);
                    break;
            }
            if ($value !== null) {
                $filter += [$type => $value];
            }
        }
        return $filter;
    }

    /**
     * Text success if review not in grid on product reviews tab
     *
     * @return string
     */
    public function toString()
    {
        return 'Review is absent in grid on product reviews tab.';
    }
}
