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

namespace Magento\Catalog\Model\Layer\Filter\DataProvider;

/**
 * Test class for \Magento\Catalog\Model\Layer\Filter\DataProvider\Price.
 *
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    protected $_model;

    protected function setUp()
    {
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        );
        $category->load(4);
        $layer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('\Magento\Catalog\Model\Layer\Category');
        $layer->setCurrentCategory($category);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Layer\Filter\DataProvider\Price', ['layer' => $layer]);
    }

    /**
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation auto
     */
    public function testGetPriceRangeAuto()
    {
        $this->assertEquals(10, $this->_model->getPriceRange());
    }

    /**
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_step 1.5
     */
    public function testGetPriceRangeManual()
    {
        // what you set is what you get
        $this->assertEquals(1.5, $this->_model->getPriceRange());
    }

    public function testGetMaxPriceInt()
    {
        $this->assertEquals(45.00, $this->_model->getMaxPrice());
    }

    /**
     * @return array
     */
    public function getRangeItemCountsDataProvider()
    {
        return [
            [1, [11 => 2, 46 => 1]],
            [10, [2 => 2, 5 => 1]],
            [20, [1 => 2, 3 => 1]],
            [50, [1 => 3]]
        ];
    }

    /**
     * @dataProvider getRangeItemCountsDataProvider
     */
    public function testGetRangeItemCounts($inputRange, $expectedItemCounts)
    {
        $actualItemCounts = $this->_model->getRangeItemCounts($inputRange);
        $this->assertEquals($expectedItemCounts, $actualItemCounts);
    }
}
