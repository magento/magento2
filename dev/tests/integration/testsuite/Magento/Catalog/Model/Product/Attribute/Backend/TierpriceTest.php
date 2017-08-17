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
     */
    public function testAfterSave()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load($this->productRepository->get('simple')->getId());
        $product->unlockAttributes();
        $product->setOrigData();
        $product->setTierPrice(
            [
                ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 2, 'price' => 7, 'delete' => true],
                ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 5, 'price' => 4],
                ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 10, 'price' => 3],
                ['website_id' => 0, 'cust_group' => 32000, 'price_qty' => 20, 'price' => 2],
            ]
        );

        $this->_model->afterSave($product);

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $fixtureProduct = $this->productRepository->get('simple');
        $product->setId($fixtureProduct->getId());
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $product->setData($linkField, $fixtureProduct->getData($linkField));
        $this->_model->afterLoad($product);
        $this->assertEquals(3, count($product->getTierPrice()));
    }

    /**
     * @depends testAfterSave
     */
    public function testAfterSaveEmpty()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->setCurrentStore(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore(
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            )
        );
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load($this->productRepository->get('simple')->getId());
        $product->setOrigData();
        $product->setTierPrice([]);
        $this->_model->afterSave($product);

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $fixtureProduct = $this->productRepository->get('simple');
        $product->setId($fixtureProduct->getId());
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $product->setData($linkField, $fixtureProduct->getData($linkField));
        $this->_model->afterLoad($product);
        $this->assertEmpty($product->getTierPrice());
    }
}
