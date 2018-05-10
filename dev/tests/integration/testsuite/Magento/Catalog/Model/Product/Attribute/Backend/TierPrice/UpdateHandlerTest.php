<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend\TierPrice;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandlerTest
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class UpdateHandlerTest extends \PHPUnit\Framework\TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $this->tierPriceFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory::class);
    }

    /**
     * @dataProvider afterSaveDataProvider
     * @param array $tierPricesData
     * @param int $isChanged
     * @param int $tierPriceCount
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testAfterSave(array $tierPricesData, $isChanged, $tierPriceCount)
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
        $product->save();
        $this->assertEquals($isChanged, $product->getData('tier_price_changed'));

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->productRepository->get('simple', true, null, true);
        $this->assertEquals($tierPriceCount, count($product->getTierPrice()));
        $this->assertEquals(0, $product->getData('tier_price_changed'));
    }

    /**
     * @return array
     */
    public function afterSaveDataProvider(): array
    {
        return [
            'same' => [
                [
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 2, 'value' => 8],
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 5, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3, 'value' => 5],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 0,
                        'qty' => 10,
                        'extension_attributes' => new \Magento\Framework\DataObject(['percentage_value' => 50])
                    ],
                ],
                0,
                4,
            ],
            'update one' => [
                [
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 2, 'value' => 8],
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 5, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3, 'value' => 5],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 0,
                        'qty' => 10,
                        'extension_attributes' => new \Magento\Framework\DataObject(['percentage_value' => 10])
                    ],
                ],
                1,
                4,
            ],
            'delete one' => [
                [
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 5, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3, 'value' => 5],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 0,
                        'qty' => 10,
                        'extension_attributes' => new \Magento\Framework\DataObject(['percentage_value' => 50])
                    ],
                ],
                1,
                3,
            ],
            'add one' => [
                [
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 2, 'value' => 8],
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 5, 'value' => 5],
                    ['website_id' => 0, 'customer_group_id' => 32000, 'qty' => 20, 'percentage_value' => 90],
                    ['website_id' => 0, 'customer_group_id' => 0, 'qty' => 3, 'value' => 5],
                    [
                        'website_id' => 0,
                        'customer_group_id' => 0,
                        'qty' => 10,
                        'extension_attributes' => new \Magento\Framework\DataObject(['percentage_value' => 50])
                    ],
                ],
                1,
                5,
            ],
            'delete all' => [[], 1, 0,],
        ];
    }
}
