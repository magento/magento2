<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

namespace Magento\Review\Model\ResourceModel\Review\Product;

use Magento\Review\Model\Review;
use Magento\TestFramework\Helper\Bootstrap;

=======
declare(strict_types=1);

namespace Magento\Review\Model\ResourceModel\Review\Product;

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
/**
 * Tests some functionality of the Product Review collection
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
<<<<<<< HEAD
     * Checks resulting ids count
     *
     * @param int $status
=======
     * @param string $status
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param int $expectedCount
     * @param string $sortAttribute
     * @param string $dir
     * @param callable $assertion
     * @dataProvider sortOrderAssertionsDataProvider
     * @magentoDataFixture Magento/Review/_files/different_reviews.php
     */
    public function testGetResultingIds(
<<<<<<< HEAD
        int $status,
=======
        ?string $status,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        int $expectedCount,
        string $sortAttribute,
        string $dir,
        callable $assertion
    ) {
<<<<<<< HEAD
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        if ($status > 0) {
=======
        /**
         * @var $collection \Magento\Review\Model\ResourceModel\Review\Product\Collection
         */
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Review\Model\ResourceModel\Review\Product\Collection::class
        );
        if ($status) {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $collection->addStatusFilter($status);
        }
        $collection->setOrder($sortAttribute, $dir);
        $actual = $collection->getResultingIds();
        $this->assertCount($expectedCount, $actual);
        $assertion($actual);
    }

    /**
<<<<<<< HEAD
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
=======
     * @return array
     */
    public function sortOrderAssertionsDataProvider() :array
    {
        return [
            [
                \Magento\Review\Model\Review::STATUS_APPROVED,
                2,
                'rt.review_id',
                'DESC',
                function (array $actual) :void {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                    $this->assertLessThan($actual[0], $actual[1]);
                }
            ],
            [
<<<<<<< HEAD
                Review::STATUS_APPROVED,
                2,
                'rt.review_id',
                'ASC',
                function (array $actual) {
=======
                \Magento\Review\Model\Review::STATUS_APPROVED,
                2,
                'rt.review_id',
                'ASC',
                function (array $actual) :void {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                    $this->assertLessThan($actual[1], $actual[0]);
                }
            ],
            [
<<<<<<< HEAD
                Review::STATUS_APPROVED,
                2,
                'rt.created_at',
                'ASC',
                function (array $actual) {
=======
                \Magento\Review\Model\Review::STATUS_APPROVED,
                2,
                'rt.created_at',
                'ASC',
                function (array $actual) :void {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                    $this->assertLessThan($actual[1], $actual[0]);
                }
            ],
            [
<<<<<<< HEAD
                0,
                3,
                'rt.review_id',
                'ASC',
                function (array $actual) {
=======
                null,
                3,
                'rt.review_id',
                'ASC',
                function (array $actual) :void {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                    $this->assertLessThan($actual[1], $actual[0]);
                }
            ]
        ];
    }
}
