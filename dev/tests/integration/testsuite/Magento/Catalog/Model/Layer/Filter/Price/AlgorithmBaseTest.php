<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter\Price;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Catalog\Model\Layer\Filter\Price.
 *
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/Price/_files/products_base.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AlgorithmBaseTest extends \PHPUnit_Framework_TestCase
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
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine mysql
     * @dataProvider pricesSegmentationDataProvider
     * @covers       \Magento\Framework\Search\Dynamic\Algorithm::calculateSeparators
     */
    public function testPricesSegmentation($categoryId, array $entityIds, array $intervalItems)
    {
        $objectManager = Bootstrap::getObjectManager();
        $layer = $objectManager->create(\Magento\Catalog\Model\Layer\Category::class);
        /** @var \Magento\Framework\Search\Request\Aggregation\TermBucket $termBucket */
        $termBucket = $objectManager->create(
            \Magento\Framework\Search\Request\Aggregation\TermBucket::class,
            ['name' => 'name', 'field' => 'price', 'metrics' => []]
        );

        $dimensions = [
            'scope' => $objectManager->create(
                \Magento\Framework\Search\Request\Dimension::class,
                ['name' => 'someName', 'value' => 'default']
            ),
        ];

        /** @var \Magento\Framework\Search\EntityMetadata $entityMetadata */
        $entityMetadata = $objectManager->create(\Magento\Framework\Search\EntityMetadata::class, ['entityId' => 'id']);
        $idKey = $entityMetadata->getEntityId();

        /** @var \Magento\Framework\Search\Adapter\Mysql\DocumentFactory $documentFactory */
        $documentFactory = $objectManager->create(
            \Magento\Framework\Search\Adapter\Mysql\DocumentFactory::class,
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

        /** @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage $temporaryStorage */
        $temporaryStorage = $objectManager->create(\Magento\Framework\Search\Adapter\Mysql\TemporaryStorage::class);
        $table = $temporaryStorage->storeDocuments($documents);

        /** @var \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $dataProvider */
        $dataProvider = $objectManager->create(
            \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider::class
        );
        $select = $dataProvider->getDataSet($termBucket, $dimensions, $table);

        /** @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\IntervalFactory $intervalFactory */
        $intervalFactory = $objectManager->create(
            \Magento\Framework\Search\Adapter\Mysql\Aggregation\IntervalFactory::class
        );
        $interval = $intervalFactory->create(['select' => $select]);

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
        $this->assertEquals(array_keys($intervalItems), array_keys($items));

        for ($i = 0; $i < count($intervalItems); ++$i) {
            $this->assertInternalType('array', $items[$i]);
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
    public function pricesSegmentationDataProvider()
    {
        $testCases = include __DIR__ . '/_files/_algorithm_base_data.php';
        $result = [];
        foreach ($testCases as $index => $testCase) {
            $result[] = [
                $index + 4, //category id
                $testCase[1],
                $testCase[2],
            ];
        }

        return $result;
    }
}
