<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierpriceTest extends TestCase
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    private $tierPriceFactory;

    /**
     * @var Tierprice
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = Bootstrap::getObjectManager()->create(
            Tierprice::class
        );
        $this->productRepository = Bootstrap::getObjectManager()->create(
            ProductRepository::class
        );
        $this->metadataPool = Bootstrap::getObjectManager()->create(
            MetadataPool::class
        );
        $this->tierPriceFactory = Bootstrap::getObjectManager()
            ->create(ProductTierPriceInterfaceFactory::class);

        $this->_model->setAttribute(
            Bootstrap::getObjectManager()->get(
                \Magento\Eav\Model\Config::class
            )->getAttribute(
                'catalog_product',
                'tier_price'
            )
        );
    }

    public function testValidate()
    {
        $product = new DataObject();
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
     */
    public function testValidateDuplicate(array $tierPricesData)
    {
        $this->expectException(LocalizedException::class);

        $product = new DataObject();
        $product->setTierPrice($tierPricesData);

        $this->_model->validate($product);
    }

    /**
     * testValidateDuplicate data provider.
     *
     * @return array
     */
    public static function validateDuplicateDataProvider(): array
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

    /**
     */
    public function testValidateDuplicateWebsite()
    {
        $this->expectException(LocalizedException::class);

        $product = new DataObject();
        $product->setTierPrice(
            [
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2.2, 'price' => 8],
                ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 5.3, 'price' => 5],
                ['website_id' => 1, 'cust_group' => 1, 'price_qty' => 5.3, 'price' => 5],
            ]
        );

        $this->_model->validate($product);
    }

    /**
     */
    public function testValidatePercentage()
    {
        $this->expectException(LocalizedException::class);

        $product = new DataObject();
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
            ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8, 'percentage_value' => 10],
            ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5, 'percentage_value' => null],
            ['website_id' => 1, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5, 'percentage_value' => 40],
            ['website_id' => 1, 'cust_group' => 1, 'price_qty' => 5.3, 'price' => 4, 'percentage_value' => 10],
            ['website_id' => 1, 'cust_group' => 1, 'price_qty' => 5.4, 'price' => 3, 'percentage_value' => 50],
            ['website_id' => 1, 'cust_group' => 1, 'price_qty' => '5.40', 'price' => 2, 'percentage_value' => null],
        ];

        $newData = $this->_model->preparePriceData($data, Type::TYPE_SIMPLE, 1);
        $this->assertCount(4, $newData);
        $this->assertArrayHasKey('1-2', $newData);
        $this->assertArrayHasKey('1-5', $newData);
        $this->assertArrayHasKey('1-5.3', $newData);
        $this->assertArrayHasKey('1-5.4', $newData);
    }

    public function testAfterLoad()
    {
        /** @var $product Product */
        $product = Bootstrap::getObjectManager()->create(
            Product::class
        );
        $fixtureProduct = $this->productRepository->get('simple');
        $product->setId($fixtureProduct->getId());
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $product->setData($linkField, $fixtureProduct->getData($linkField));
        $this->_model->afterLoad($product);
        $price = $product->getTierPrice();
        $this->assertNotEmpty($price);
        $this->assertCount(5, $price);
    }

    /**
     * @dataProvider saveExistingProductDataProvider
     * @param array $tierPricesData
     * @param int $tierPriceCount
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testSaveExistingProduct(array $tierPricesData, int $tierPriceCount): void
    {
        /** @var $product Product */
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
    public static function saveExistingProductDataProvider(): array
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
                        'extension_attributes' => new DataObject(['percentage_value' => 50])
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
                        'extension_attributes' => new DataObject(['percentage_value' => 10])
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
                        'extension_attributes' => new DataObject(['percentage_value' => 50])
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
                        'extension_attributes' => new DataObject(['percentage_value' => 90])
                    ],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 0,
                        'qty' => 10,
                        'extension_attributes' => new DataObject(['percentage_value' => 50])
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
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    public function testSaveNewProduct(array $tierPricesData, int $tierPriceCount): void
    {
        /** @var $product Product */
        $product = Bootstrap::getObjectManager()
            ->create(Product::class);
        $product->isObjectNew(true);
        $product->setTypeId(Type::TYPE_SIMPLE)
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
    public static function saveNewProductDataProvider(): array
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
                        'extension_attributes' => new DataObject(['percentage_value' => 50])
                    ],
                ],
                6,
            ],
        ];
    }
}
