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

/**
 * Test class for \Magento\Catalog\Model\Layer\Filter\Price.
 *
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/Price/_files/products_base.php
 */
class AlgorithmBaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Algorithm model
     *
     * @var \Magento\Catalog\Model\Layer\Filter\Price\Algorithm
     */
    protected $_model;

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

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Layer\Filter\Price\Algorithm');
        $this->_layer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Layer\Category');
        $this->_filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Layer\Filter\Price', array('layer' => $this->_layer));
        $this->_filter->setAttributeModel(new \Magento\Framework\Object(array('attribute_code' => 'price')));
    }

    /**
     * @dataProvider pricesSegmentationDataProvider
     */
    public function testPricesSegmentation($categoryId, $intervalsNumber, $intervalItems)
    {
        $this->_layer->setCurrentCategory($categoryId);
        $collection = $this->_layer->getProductCollection();

        $memoryUsedBefore = memory_get_usage();
        $this->_model->setPricesModel(
            $this->_filter
        )->setStatistics(
            $collection->getMinPrice(),
            $collection->getMaxPrice(),
            $collection->getPriceStandardDeviation(),
            $collection->getSize()
        );
        if (!is_null($intervalsNumber)) {
            $this->assertEquals($intervalsNumber, $this->_model->getIntervalsNumber());
        }

        $items = $this->_model->calculateSeparators();
        $this->assertEquals(array_keys($intervalItems), array_keys($items));

        for ($i = 0; $i < count($intervalItems); ++$i) {
            $this->assertInternalType('array', $items[$i]);
            $this->assertEquals($intervalItems[$i]['from'], $items[$i]['from']);
            $this->assertEquals($intervalItems[$i]['to'], $items[$i]['to']);
            $this->assertEquals($intervalItems[$i]['count'], $items[$i]['count']);
        }

        // Algorythm should use less than 10M
        $this->assertLessThan(10 * 1024 * 1024, memory_get_usage() - $memoryUsedBefore);
    }

    public function pricesSegmentationDataProvider()
    {
        $testCases = include __DIR__ . '/_files/_algorithm_base_data.php';
        $result = array();
        foreach ($testCases as $index => $testCase) {
            $result[] = array(
                $index + 4, //category id
                $testCase[1],
                $testCase[2]
            );
        }

        return $result;
    }
}
