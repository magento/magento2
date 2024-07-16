<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Review\Ui\DataProvider\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Api\Filter;

/**
 * Test for \Magento\Review\Ui\DataProvider\Product\ReviewDataProvider.
 */
class ReviewDataProviderTest extends TestCase
{
    /**
     * @var array
     */
    private $modelParams = [
        'name' => 'review_listing_data_source',
        'primaryFieldName' => 'review_id',
        'requestFieldName' => 'entity_id',
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Sorting dataProvider test
     *
     * @magentoDataFixture Magento/Review/_files/different_reviews.php
     * @dataProvider sortingDataProvider
     *
     * @param string $field
     * @param string $direction
     * @param array $expectedSortedTitles
     * @return void
     */
    public function testSorting(string $field, string $direction, array $expectedSortedTitles): void
    {
        $request = $this->objectManager->create(RequestInterface::class);
        $request->setParam('current_product_id', 1);

        $dataProvider = $this->objectManager->create(
            ReviewDataProvider::class,
            array_merge($this->modelParams, ['request' => $request])
        );
        $dataProvider->addOrder($field, $direction);
        $result = $dataProvider->getData();

        $this->assertEquals($this->getItemsField($result, 'title'), $expectedSortedTitles);
    }

    /**
     * Return items field data
     *
     * @param array $arrItems
     * @param string $field
     * @return array
     */
    private function getItemsField(array $arrItems, string $field): array
    {
        $data = [];
        foreach ($arrItems['items'] as $review) {
            $data[] = $review[$field];
        }

        return $data;
    }

    /**
     * DataProvider for testSorting
     *
     * @return array
     */
    public static function sortingDataProvider(): array
    {
        return [
            'sort by title field ascending' => [
                'title',
                'asc',
                ['1 filter second review', '2 filter first review', 'Review Summary'],
            ],
            'sort by title field descending' => [
                'title',
                'desc',
                ['Review Summary', '2 filter first review', '1 filter second review'],
            ],
        ];
    }

    /**
     * Filter dataProvider test
     *
     * @magentoDataFixture Magento/Review/_files/different_reviews.php
     *
     * @return void
     */
    public function testFilter(): void
    {
        $searchTitle = '2 filter first review';

        $request = $this->objectManager->create(RequestInterface::class);
        $request->setParam('current_product_id', 1);

        /** @var ReviewDataProvider $dataProvider */
        $dataProvider = $this->objectManager->create(
            ReviewDataProvider::class,
            array_merge($this->modelParams, ['request' => $request])
        );

        /** @var Filter $filter */
        $filter = $this->objectManager->create(Filter::class);
        $filter->setField('title')
            ->setValue($searchTitle);

        $dataProvider->addFilter($filter);
        $result = $dataProvider->getData();

        $this->assertEquals($this->getItemsField($result, 'title'), [$searchTitle]);
    }
}
