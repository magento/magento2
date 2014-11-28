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
namespace Magento\Catalog\Model\Layer\Filter\Price;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Catalog\Model\Layer\Filter\Price.
 *
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/Price/_files/products_base.php
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
     * @var \Magento\Catalog\Model\Resource\Layer\Filter\Price
     */
    protected $priceResource;

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine Magento\CatalogSearch\Model\Resource\Engine
     * @dataProvider pricesSegmentationDataProvider
     * @covers \Magento\Framework\Search\Dynamic\Algorithm::calculateSeparators
     */
    public function testPricesSegmentation($categoryId, array $entityIds, array $intervalItems)
    {
        $objectManager = Bootstrap::getObjectManager();
        $layer = $objectManager->create('Magento\Catalog\Model\Layer\Category');
        /** @var \Magento\Framework\Search\Request\Aggregation\TermBucket $termBucket */
        $termBucket = $objectManager->create(
            'Magento\Framework\Search\Request\Aggregation\TermBucket',
            ['name' => 'name', 'field' => 'price', 'metrics' => []]
        );

        $dimensions = [
            'scope' => $objectManager->create(
                'Magento\Framework\Search\Request\Dimension',
                ['name' => 'someName', 'value' => 'default']
            )
        ];

        /** @var \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $dataProvider */
        $dataProvider = $objectManager->create('Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider');
        $select = $dataProvider->getDataSet($termBucket, $dimensions);
        $select->where('main_table.entity_id IN (?)', $entityIds);

        /** @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\IntervalFactory $intervalFactory */
        $intervalFactory = $objectManager->create('Magento\Framework\Search\Adapter\Mysql\Aggregation\IntervalFactory');
        $interval = $intervalFactory->create(['select' => $select]);

        /** @var \Magento\Framework\Search\Dynamic\Algorithm $model */
        $model = $objectManager->create('Magento\Framework\Search\Dynamic\Algorithm');

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
                $testCase[2]
            ];
        }

        return $result;
    }
}
