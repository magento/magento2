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

namespace Magento\Review\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Mtf\Constraint\AbstractConstraint;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;

/**
 * Class AssertProductReviewNotInGrid
 * Check that Product Review not available in grid
 */
class AssertProductReviewNotInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
        'sku'
    ];

    /**
     * Asserts Product Review not available in grid
     *
     * @param ReviewIndex $reviewIndex
     * @param ReviewInjectable $review
     * @param string $gridStatus
     * @param ReviewInjectable $reviewInitial
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        ReviewInjectable $review,
        $gridStatus = '',
        ReviewInjectable $reviewInitial = null
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
     * @param ReviewInjectable $review
     * @param string $gridStatus
     * @return array
     */
    public function prepareFilter(FixtureInterface $product, ReviewInjectable $review, $gridStatus)
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
