<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter\Price;

use Magento\Framework\Search\Dynamic\IntervalInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Catalog\Model\Layer\Filter\Price.
 *
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/Price/_files/products_base.php
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AlgorithmBaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Layer model
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_layer;

    /**
     * Price filter model
     *
     * @var \Magento\Catalog\Model\Layer\Filter\Price
     */
    protected $_filter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    protected $priceResource;

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @dataProvider pricesSegmentationDataProvider
     * @param $categoryId
     * @param array $entityIds
     * @param array $intervalItems
     * @covers \Magento\Framework\Search\Dynamic\Algorithm::calculateSeparators
     */
    public function testPricesSegmentation($categoryId, array $entityIds, array $intervalItems)
    {
        $this->markTestSkipped('MC-33826:'
        . 'Stabilize skipped test cases for Integration AlgorithmBaseTest with elasticsearch');
        $objectManager = Bootstrap::getObjectManager();
        $layer = $objectManager->create(\Magento\Catalog\Model\Layer\Category::class);

        /** @var \Magento\Framework\Search\EntityMetadata $entityMetadata */
        $entityMetadata = $objectManager->create(\Magento\Framework\Search\EntityMetadata::class, ['entityId' => 'id']);
        $idKey = $entityMetadata->getEntityId();

        // this class has been removed
        /** @var \Magento\Elasticsearch\SearchAdapter\DocumentFactory $documentFactory */
        $documentFactory = $objectManager->create(
            \Magento\Elasticsearch\SearchAdapter\DocumentFactory::class,
            ['entityMetadata' => $entityMetadata]
        );

        /** @var \Magento\Framework\Api\Search\Document[] $documents */
        $documents = [];
        foreach ($entityIds as $entityId) {
            $rawDocument = [
                $idKey => $entityId,
                'score' => 1,
            ];
            $documents[] = $documentFactory->create($rawDocument);
        }
        /** @var \Magento\CatalogSearch\Model\Price\IntervalFactory $intervalFactory */
        $intervalFactory = $objectManager->create(
            \Magento\CatalogSearch\Model\Price\IntervalFactory::class
        );
        /** @var \Magento\CatalogSearch\Model\Price\Interval $interval */
        $interval = $intervalFactory->create();

        /** @var \Magento\Framework\Search\Dynamic\Algorithm $model */
        $model = $objectManager->create(\Magento\Framework\Search\Dynamic\Algorithm::class);

        $layer->setCurrentCategory($categoryId);
        $collection = $layer->getProductCollection();

        $memoryUsedBefore = memory_get_usage();
        $model->setStatistics(
            $collection->getMinPrice(),
            $collection->getMaxPrice(),
            $collection->getPriceStandardDeviation(),
            $collection->getPricesCount()
        );

        $items = $model->calculateSeparators($interval);
        $this->assertEquals($intervalItems, $items);

        for ($i = 0, $count = count($intervalItems); $i < $count; ++$i) {
            $this->assertIsArray($items[$i]);
            $this->assertEquals($intervalItems[$i]['from'], $items[$i]['from']);
            $this->assertEquals($intervalItems[$i]['to'], $items[$i]['to']);
            $this->assertEquals($intervalItems[$i]['count'], $items[$i]['count']);
        }

        // Algorithm should use less than 10M
        $this->assertLessThan(10 * 1024 * 1024, memory_get_usage() - $memoryUsedBefore);
    }

    /**
     * @return array
     */
    public static function pricesSegmentationDataProvider()
    {
        $testCases = include __DIR__ . '/_files/_algorithm_base_data.php';
        $testCasesNew = self::getUnSkippedTestCases($testCases);
        $result = [];
        foreach ($testCasesNew as $index => $testCase) {
            $result[] = [
                $index + 4, //category id
                $testCase[1],
                $testCase[2],
            ];
        }
        return $result;
    }

    /**
     * Get unSkipped test cases from dataProvider
     *
     * @param array $testCases
     * @return array
     */
    private static function getUnSkippedTestCases(array $testCases) : array
    {
        // TO DO UnSkip skipped test cases and remove this function
        $SkippedTestCases = [];
        $UnSkippedTestCases = [];
        foreach ($testCases as $testCase) {
            if (array_key_exists('incomplete_reason', $testCase)) {
                if ($testCase['incomplete_reason'] === " ") {
                    $UnSkippedTestCases [] = $testCase;
                } else {
                    if ($testCase['incomplete_reason'] != " ") {
                        $SkippedTestCases [] = $testCase;
                    }
                }
            }
        }
        return $UnSkippedTestCases;
    }
}
