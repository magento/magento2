<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory
     */
    private $tierPriceFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice
     */
    protected $_model;

    protected function setUp(): void
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
        $this->tierPriceFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory::class);

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
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 5.6, 'price' => 4],
            ]
        );
        $this->assertTrue($this->_model->validate($product));
    }

    /**
     * Test that duplicated tier price values issues exception during validation.
     *
     * @dataProvider validateDuplicateDataProvider
     *
     */
    public function testValidateDuplicate(array $tierPricesData)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $product = new \Magento\Framework\DataObject();
        $product->setTierPrice($tierPricesData);

        $this->_model->validate($product);
    }

    /**
     * testValidateDuplicate data provider.
     *
     * @return array
     */
    public function validateDuplicateDataProvider(): array
    {
        return [
            [
                [
                    ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8],
                    ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8],
                ],
            ],
            [
                [
                    ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2.2, 'price' => 8],
                    ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2.2, 'price' => 8],
                ],
            ],
        ];
    }

    public function testValidateDuplicateWebsite()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $product = new \Magento\Framework\DataObject();
        $product->setTierPrice(
            [
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2.2, 'price' => 8],
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 5.3, 'price' => 5],
                ['website_id' => 1, 'cust_group' => 1, 'price_qty' => 5.3, 'price' => 5],
            ]
        );

        $this->_model->validate($product);
    }

    public function testValidatePercentage()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
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
            ['website_id' => 1, 'cust_group' => 1, 'price_qty' => 5.3, 'price' => 4],
            ['website_id' => 1, 'cust_group' => 1, 'price_qty' => 5.4, 'price' => 3],
            ['website_id' => 1, 'cust_group' => 1, 'price_qty' => '5.40', 'price' => 2],
        ];

        $newData = $this->_model->preparePriceData($data, \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, 1);
        $this->assertEquals(4, count($newData));
        $this->assertArrayHasKey('1-2', $newData);
        $this->assertArrayHasKey('1-5', $newData);
        $this->assertArrayHasKey('1-5.3', $newData);
        $this->assertArrayHasKey('1-5.4', $newData);
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
        $this->assertEquals(5, count($price));
    }

    /**
     * @dataProvider saveExistingProductDataProvider
     * @param array $tierPricesData
     * @param int $tierPriceCount
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testSaveExistingProduct(array $tierPricesData, int $tierPriceCount): void
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->productRepository->get('simple', true);
        $tierPrices = [];
        foreach ($tierPricesData as $tierPrice) {
            $tierPrices[] = $this->tierPriceFactory->create([
                'data' => $tierPrice
            ]);
        }
        $product->setTierPrices($tierPrices);
        $product = $this->productRepository->save($product);
        $this->assertEquals($tierPriceCount, count($product->getTierPrice()));
        $this->assertEquals(0, $product->getData('tier_price_changed'));
    }

    /**
     * @return array
     */
    public function saveExistingProductDataProvider(): array
    {
        return [
            'same' => [
                [
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 2, 'value' => 8],
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 5, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3.2, 'value' => 6],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 0,
                        'qty' => 10,
                        'extension_attributes' => new \Magento\Framework\DataObject(['percentage_value' => 50])
                    ],
                ],
                5,
            ],
            'update one' => [
                [
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 2, 'value' => 8],
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 5, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => '3.2', 'value' => 6],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 0,
                        'qty' => 10,
                        'extension_attributes' => new \Magento\Framework\DataObject(['percentage_value' => 10])
                    ],
                ],
                5,
            ],
            'delete one' => [
                [
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 5, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => '3.2', 'value' => 6],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 0,
                        'qty' => 10,
                        'extension_attributes' => new \Magento\Framework\DataObject(['percentage_value' => 50])
                    ],
                ],
                4,
            ],
            'add one' => [
                [
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 2, 'value' => 8],
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 5, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3.2, 'value' => 6],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 32000,
                        'qty' => 20,
                        'extension_attributes' => new \Magento\Framework\DataObject(['percentage_value' => 90])
                    ],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 0,
                        'qty' => 10,
                        'extension_attributes' => new \Magento\Framework\DataObject(['percentage_value' => 50])
                    ],
                ],
                6,
            ],
            'delete all' => [[], 0,],
        ];
    }

    /**
     * @dataProvider saveNewProductDataProvider
     * @param array $tierPricesData
     * @param int $tierPriceCount
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testSaveNewProduct(array $tierPricesData, int $tierPriceCount): void
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Product::class);
        $product->isObjectNew(true);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->setAttributeSetId(4)
            ->setName('Simple Product New')
            ->setSku('simple product new')
            ->setPrice(10);
        $tierPrices = [];
        foreach ($tierPricesData as $tierPrice) {
            $tierPrices[] = $this->tierPriceFactory->create([
                'data' => $tierPrice,
            ]);
        }
        $product->setTierPrices($tierPrices);
        $product = $this->productRepository->save($product);
        $this->assertEquals($tierPriceCount, count($product->getTierPrice()));
        $this->assertEquals(0, $product->getData('tier_price_changed'));
    }

    /**
     * @return array
     */
    public function saveNewProductDataProvider(): array
    {
        return [
            [
                [
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 2, 'value' => 8],
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 5, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => '3.2', 'value' => 4],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => '3.3', 'value' => 3],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 0,
                        'qty' => 10,
                        'extension_attributes' => new \Magento\Framework\DataObject(['percentage_value' => 50])
                    ],
                ],
                6,
            ],
        ];
    }
}
