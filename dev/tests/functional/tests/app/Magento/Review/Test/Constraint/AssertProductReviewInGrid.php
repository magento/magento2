<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Check that review is displayed in grid.
 */
class AssertProductReviewInGrid extends AbstractConstraint
{
    /**
     * Filter params.
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
     * Assert that review is displayed in grid.
     *
     * @param ReviewIndex $reviewIndex
     * @param Review $review
     * @param FixtureInterface $product
     * @param string $gridStatus
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        Review $review,
        FixtureInterface $product,
        $gridStatus = ''
    ) {
        $filter = $this->prepareFilter($product, $review->getData(), $gridStatus);

        $reviewIndex->open();
        $reviewIndex->getReviewGrid()->search($filter);
        unset($filter['visible_in']);
        \PHPUnit_Framework_Assert::assertTrue(
            $reviewIndex->getReviewGrid()->isRowVisible($filter, false),
            'Review is absent in Review grid.'
        );
    }

    /**
     * Prepare filter for assert.
     *
     * @param FixtureInterface $product
     * @param array $review
     * @param string $gridStatus [optional]
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareFilter(FixtureInterface $product, array $review, $gridStatus = '')
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
                    $value = isset($review[$param]) ? $review[$param][0] : null;
                    break;
                case 'status_id':
                    $value = $gridStatus != '' ? $gridStatus : (isset($review[$param]) ? $review[$param] : null);
                    break;
                case 'type':
                    $value = isset($review[$param]) ? $review[$param] : 'Administrator';
                    break;
                default:
                    $value = isset($review[$param]) ? $review[$param] : null;
                    break;
            }
            if ($value !== null) {
                $filter += [$type => $value];
            }
        }
        return $filter;
    }

    /**
     * Text success exist review in grid on product reviews tab.
     *
     * @return string
     */
    public function toString()
    {
        return 'Review is present in grid on product reviews tab.';
    }
}
