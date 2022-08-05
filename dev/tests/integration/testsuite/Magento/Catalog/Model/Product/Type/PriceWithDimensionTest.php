<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation disabled
 * @magentoIndexerDimensionMode catalog_product_price website_and_customer_group
 * @group indexer_dimension
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class PriceWithDimensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Type\Price
     */
    protected $_model;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->_model = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Type\Price::class
        );
    }

    /**
     * Get price from indexer
     */
    public function testGetPriceFromIndexer()
    {
        /** @var PriceTableResolver $tableResolver */
        $tableResolver = Bootstrap::getObjectManager()->create(PriceTableResolver::class);

        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = Bootstrap::getObjectManager()->create(ResourceConnection::class);

        /** @var DimensionFactory $dimensionFactory */
        $dimensionFactory = Bootstrap::getObjectManager()->create(DimensionFactory::class);
        $dimension = [
            $dimensionFactory->create(CustomerGroupDimensionProvider::DIMENSION_NAME, (string)0),
            $dimensionFactory->create(WebsiteDimensionProvider::DIMENSION_NAME, (string)1)
        ];
        $connection = $resourceConnection->getConnection();
        $priceTable = $connection->getTableName(
            $tableResolver->resolve('catalog_product_index_price', $dimension)
        );

        $select = $connection->select()->from($priceTable)->where('entity_id = 1');

        $return = $connection->fetchAll($select);

        $this->assertEquals(10, $return[0]['price']);
        $this->assertEquals(10, $return[0]['final_price']);
        $this->assertEquals(19, $return[0]['min_price']);
        $this->assertEquals(19, $return[0]['max_price']);
    }

    /**
     * Get price
     */
    public function testGetPrice()
    {
        $this->assertEquals('test', $this->_model->getPrice(new DataObject(['price' => 'test'])));
    }

    /**
     * Get final price
     */
    public function testGetFinalPrice()
    {
        $repository = Bootstrap::getObjectManager()->create(
            ProductRepository::class
        );
        $product = $repository->get('simple');
        // fixture

        // regular & tier prices
        $this->assertEquals(10.0, $this->_model->getFinalPrice(1, $product));
        $this->assertEquals(8.0, $this->_model->getFinalPrice(2, $product));
        $this->assertEquals(5.0, $this->_model->getFinalPrice(5, $product));

        // with options
        $buyRequest = $this->prepareBuyRequest($product);
        $product->getTypeInstance()->prepareForCart($buyRequest, $product);

        //product price + options price(10+1+2+3+3)
        $this->assertEquals(19.0, $this->_model->getFinalPrice(1, $product));

        //product tier price + options price(5+1+2+3+3)
        $this->assertEquals(14.0, $this->_model->getFinalPrice(5, $product));
    }

    /**
     * Get formatted price
     */
    public function testGetFormatedPrice()
    {
        $repository = Bootstrap::getObjectManager()->create(
            ProductRepository::class
        );
        $product = $repository->get('simple');
        // fixture
        $this->assertEquals('<span class="price">$10.00</span>', $this->_model->getFormatedPrice($product));
    }

    /**
     * Calculate price
     */
    public function testCalculatePrice()
    {
        $this->assertEquals(10, $this->_model->calculatePrice(10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01'));
        $this->assertEquals(8, $this->_model->calculatePrice(10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01'));
    }

    /**
     * Calculate special price
     */
    public function testCalculateSpecialPrice()
    {
        $this->assertEquals(
            10,
            $this->_model->calculateSpecialPrice(10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01')
        );
        $this->assertEquals(
            8,
            $this->_model->calculateSpecialPrice(10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01')
        );
    }

    /**
     * Is tier price fixed
     */
    public function testIsTierPriceFixed()
    {
        $this->assertTrue($this->_model->isTierPriceFixed());
    }

    /**
     * Build buy request based on product custom options
     *
     * @param Product $product
     * @return DataObject
     */
    private function prepareBuyRequest(Product $product)
    {
        $options = [];
        /** @var $option \Magento\Catalog\Model\Product\Option */
        foreach ($product->getOptions() as $option) {
            switch ($option->getGroupByType()) {
                case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_GROUP_DATE:
                    $value = ['year' => 2013, 'month' => 8, 'day' => 9, 'hour' => 13, 'minute' => 35];
                    break;
                case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_GROUP_SELECT:
                    $value = key($option->getValues());
                    break;
                default:
                    $value = 'test';
                    break;
            }
            $options[$option->getId()] = $value;
        }

        return new DataObject(['qty' => 1, 'options' => $options]);
    }
}
