<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider;

use Magento\Framework\Data\Collection;

/**
 * @magentoAppArea adminhtml
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
     */
    private $dataProvider;

    protected function setUp()
    {
        $this->dataProvider = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider::class,
            [
                'name'=> 'products',
                'primaryFieldName' => 'entity_id',
                'requestFieldName' => 'id',
            ]
        );
    }

    /**
     * @dataProvider sortingFieldsDataProvider
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     *
     * @param string $orderByField
     * @param string $direction
     */
    public function testSortingNotAffectsCount($orderByField, $direction)
    {
        $this->dataProvider->addOrder($orderByField, $direction);
        $result = $this->dataProvider->getData();
        $this->assertEquals(3, $result['totalRecords']);
    }

    /**
     * @return array
     */
    public function sortingFieldsDataProvider()
    {
        return [
            'name ASC' => ['name', Collection::SORT_ORDER_ASC],
            'name DESC' => ['name', Collection::SORT_ORDER_DESC],
            'sku ASC' => ['sku', Collection::SORT_ORDER_ASC],
            'sku DESC' => ['sku', Collection::SORT_ORDER_DESC],
            'price ASC' => ['price', Collection::SORT_ORDER_ASC],
            'price DESC' => ['price', Collection::SORT_ORDER_DESC],
        ];
    }
}
