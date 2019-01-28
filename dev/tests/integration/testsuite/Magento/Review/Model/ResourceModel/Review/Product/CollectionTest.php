<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Model\ResourceModel\Review\Product;

use Magento\Review\Model\Review;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests some functionality of the Product Review collection
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Checks resulting ids count
     *
     * @param int $status
     * @param int $expectedCount
     * @param string $sortAttribute
     * @param string $dir
     * @param callable $assertion
     * @dataProvider sortOrderAssertionsDataProvider
     * @magentoDataFixture Magento/Review/_files/different_reviews.php
     */
    public function testGetResultingIds(
        int $status,
        int $expectedCount,
        string $sortAttribute,
        string $dir,
        callable $assertion
    ) {
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        if ($status > 0) {
            $collection->addStatusFilter($status);
        }
        $collection->setOrder($sortAttribute, $dir);
        $actual = $collection->getResultingIds();
        $this->assertCount($expectedCount, $actual);
        $assertion($actual);
    }

    /**
     * Sort order assertions data provider
     *
     * @return array
     */
    public function sortOrderAssertionsDataProvider(): array
    {
        return [
            [
                Review::STATUS_APPROVED,
                2,
                'rt.review_id',
                'DESC',
                function (array $actual) {
                    $this->assertLessThan($actual[0], $actual[1]);
                }
            ],
            [
                Review::STATUS_APPROVED,
                2,
                'rt.review_id',
                'ASC',
                function (array $actual) {
                    $this->assertLessThan($actual[1], $actual[0]);
                }
            ],
            [
                Review::STATUS_APPROVED,
                2,
                'rt.created_at',
                'ASC',
                function (array $actual) {
                    $this->assertLessThan($actual[1], $actual[0]);
                }
            ],
            [
                0,
                3,
                'rt.review_id',
                'ASC',
                function (array $actual) {
                    $this->assertLessThan($actual[1], $actual[0]);
                }
            ]
        ];
    }
}
