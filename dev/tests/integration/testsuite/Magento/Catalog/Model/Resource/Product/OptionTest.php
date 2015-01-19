<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Product;

class OptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected function setUp()
    {
        /** @var \Magento\Catalog\Model\Resource\Product $productResource */
        $this->productResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Resource\Product'
        );
    }

    /**
     * Data provider for testSaveTitle
     *
     * @return array
     */
    public function saveTitleDataProvider()
    {
        return [
            [
                [
                    [
                        'is_delete' => NULL,
                        'previous_type' => 'drop_down',
                        'previous_group' => 'select',
                        'sort_order' => '0',
                        'title' => 'custom option view',
                        'type' => 'drop_down',
                        'is_require' => '1',
                        'values' => [
                            [
                                'sort_order' => '0',
                                'option_type_id' => '-1',
                                'is_delete' => NULL,
                                'title' => '1 custom option title second view',
                                'price' => '10.00',
                                'price_type' => 'fixed',
                                'sku' => NULL,
                            ],
                        ],
                    ],
                ],
                2,
                false,
            ],
            [
                [
                    [
                        'is_delete' => NULL,
                        'previous_type' => 'drop_down',
                        'previous_group' => 'select',
                        'sort_order' => '0',
                        'title' => 'custom option view',
                        'type' => 'drop_down',
                        'is_require' => '1',
                        'values' => [
                            [
                                'sort_order' => '0',
                                'option_type_id' => '-1',
                                'is_delete' => NULL,
                                'title' => '2 custom option title',
                                'price' => '10.00',
                                'price_type' => 'fixed',
                                'sku' => NULL,
                            ],
                        ],
                    ],
                ],
                \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                true,
            ],
        ];
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     * @dataProvider saveTitleDataProvider
     */
    public function testSaveTitle($options, $storeId, $result)
    {
        $productId = 1;
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
        $store->load('fixture_second_store');

        $title = $options[0]['values'][0]['title'];
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load($productId);
        $product->setStoreIds([$storeId]);
        $product->setProductOptions($options);
        $product->setCanSaveCustomOptions(true);
        $product->save();

        $typeTitle = $this->productResource->getTable('catalog_product_option_type_title');
        $typeValue = $this->productResource->getTable('catalog_product_option_type_value');
        $typeOption = $this->productResource->getTable('catalog_product_option');

        $select = $this->productResource->getReadConnection()->select()->from(
            ['t' => $typeTitle],
            ['title']
        )->join(
            ['ov' => $typeValue],
            't.option_type_id = ov.option_type_id'
        )->join(
            ['o' => $typeOption],
            'ov.option_id = o.option_id'
        )->where(
            'o.product_id = ?',
            $productId
        )->where(
            't.store_id = ?',
            $storeId
        )->where(
            't.title = ?',
            $title
        );

        $testResult = $this->productResource->getReadConnection()->fetchOne($select);
        $this->assertEquals($result, (bool)$testResult);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     * @dataProvider saveTitleDataProvider
     */
    public function testSavePrice($options, $storeId, $result)
    {
        $productId = 1;
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
        $store->load('fixture_second_store');

        $price = $options[0]['values'][0]['price'];
        $priceType = $options[0]['values'][0]['price_type'];
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load($productId);
        $product->setStoreIds([$storeId]);
        $product->setProductOptions($options);
        $product->setCanSaveCustomOptions(true);
        $product->save();

        $typePrice = $this->productResource->getTable('catalog_product_option_type_price');
        $typeValue = $this->productResource->getTable('catalog_product_option_type_value');
        $typeOption = $this->productResource->getTable('catalog_product_option');

        $select = $this->productResource->getReadConnection()->select()->from(
            ['p' => $typePrice],
            ['price', 'price_type']
        )->join(
            ['ov' => $typeValue],
            'p.option_type_id = ov.option_type_id'
        )->join(
            ['o' => $typeOption],
            'ov.option_id = o.option_id'
        )->where(
            'o.product_id = ?',
            $productId
        )->where(
            'p.store_id = ?',
            $storeId
        )->where(
            'p.price = ?',
            $price
        )->where(
            'p.price_type = ?',
            $priceType
        );

        $testResult = $this->productResource->getReadConnection()->fetchOne($select);
        $this->assertEquals($result, (bool)$testResult);
    }
}
