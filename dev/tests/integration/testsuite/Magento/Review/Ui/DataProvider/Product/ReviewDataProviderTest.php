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
     * @param array $sorting
     * @param array $expectedSortedTitles
     * @return void
     */
    public function testSorting(array $sorting, array $expectedSortedTitles): void
    {
        $request = $this->objectManager->create(RequestInterface::class);
        $request->setParam('sorting', $sorting);
        $request->setParam('current_product_id', 1);

        $dataProvider = $this->objectManager->create(
            ReviewDataProvider::class,
            array_merge($this->modelParams, ['request' => $request])
        );

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
    public function sortingDataProvider(): array
    {
        return [
            [
                ['field' => 'title', 'direction' => 'asc'],
                ['1 filter second review', '2 filter first review', 'Review Summary'],
            ],
            [
                ['field' => 'title', 'direction' => 'desc'],
                ['Review Summary', '2 filter first review', '1 filter second review'],
            ],
        ];
    }
}
