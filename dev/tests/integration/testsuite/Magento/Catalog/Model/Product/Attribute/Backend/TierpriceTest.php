<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class TierpriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice::class
        );
        $this->productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $this->metadataPool = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\EntityManager\MetadataPool::class
        );
        $this->_model->setAttribute(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Eav\Model\Config::class
            )->getAttribute(
                'catalog_product',
                'tier_price'
            )
        );
    }

    public function testValidate()
    {
        $product = new \Magento\Framework\DataObject();
        $product->setTierPrice(
            [
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8],
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5],
            ]
        );
        $this->assertTrue($this->_model->validate($product));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateDuplicate()
    {
        $product = new \Magento\Framework\DataObject();
        $product->setTierPrice(
            [
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8],
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8],
            ]
        );

        $this->_model->validate($product);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateDuplicateWebsite()
    {
        $product = new \Magento\Framework\DataObject();
        $product->setTierPrice(
            [
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8],
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5],
                ['website_id' => 1, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5],
            ]
        );

        $this->_model->validate($product);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testValidatePercentage()
    {
        $product = new \Magento\Framework\DataObject();
        $product->setTierPrice(
            [
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'percentage_value' => 101],
            ]
        );

        $this->_model->validate($product);
    }

    public function testPreparePriceData()
    {
        $data = [
            ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8],
            ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5],
            ['website_id' => 1, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5],
        ];

        $newData = $this->_model->preparePriceData($data, \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, 1);
        $this->assertEquals(2, count($newData));
        $this->assertArrayHasKey('1-2', $newData);
        $this->assertArrayHasKey('1-5', $newData);
    }

    public function testAfterLoad()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $fixtureProduct = $this->productRepository->get('simple');
        $product->setId($fixtureProduct->getId());
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $product->setData($linkField, $fixtureProduct->getData($linkField));
        $this->_model->afterLoad($product);
        $price = $product->getTierPrice();
        $this->assertNotEmpty($price);
        $this->assertEquals(4, count($price));
    }

    /**
     * @magentoAppArea adminhtml
     * @param array $tierPrice
     * @param bool $isChanged
     * @param int $tierPriceCtr
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave($tierPrice, $isChanged, $tierPriceCtr)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load($this->productRepository->get('simple')->getId());
        $product->unlockAttributes();
        // Added tier price
        $product->setTierPrice($tierPrice);

        $this->_model->afterSave($product);
        $this->assertEquals($isChanged, $product->getData('tier_price_changed'));

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $fixtureProduct = $this->productRepository->get('simple');
        $product->setId($fixtureProduct->getId());
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $product->setData($linkField, $fixtureProduct->getData($linkField));
        $this->_model->afterLoad($product);
        $this->assertEquals($tierPriceCtr, count($product->getTierPrice()));
        $this->assertEquals(0, $product->getData('tier_price_changed'));
    }

    public function afterSaveDataProvider()
    {
        return [
            'same' => [
                [
                    ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 2, 'price' => 8],
                    ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 5, 'price' => 5],
                    ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 3, 'price' => 5],
                    ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 10, 'percentage_value' => 50],
                ],
                0,
                4,
            ],
            'update one' => [
                [
                    ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 2, 'price' => 8],
                    ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 5, 'price' => 5],
                    ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 3, 'price' => 5],
                    ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 10, 'percentage_value' => 10],
                ],
                1,
                4,
            ],
            'delete one' => [
                [
                    ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 2, 'price' => 8, 'delete' => true],
                    ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 5, 'price' => 5],
                    ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 3, 'price' => 5],
                    ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 10, 'percentage_value' => 50],
                ],
                1,
                3,
            ],
            'add one' => [
                [
                    ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 2, 'price' => 8],
                    ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 5, 'price' => 5],
                    ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 20, 'percentage_value' => 90],
                    ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 3, 'price' => 5],
                    ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 10, 'percentage_value' => 50],
                ],
                1,
                5,
            ],
            'delete all' => [[], 1, 0,],
        ];
    }
}
