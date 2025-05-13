<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Catalog\Ui\Component\Listing\Columns\Price;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Related;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test related/upsell/crosssel products UI modifier
 */
class RelatedTest extends AbstractModifierTestCase
{
    /**
     * @var ProductLinkRepositoryInterface|MockObject
     */
    private $productLinkRepository;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var ImageHelper|MockObject
     */
    private $imageHelper;

    /**
     * @var Status|MockObject
     */
    private $status;

    /**
     * @var AttributeSetRepositoryInterface|MockObject
     */
    private $attributeSetRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productLinkRepository = $this->createMock(ProductLinkRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->imageHelper = $this->createMock(ImageHelper::class);
        $this->status = $this->createMock(Status::class);
        $this->attributeSetRepository = $this->createMock(AttributeSetRepositoryInterface::class);
    }

    /**
     * @return Related
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Related::class, [
            'locator' => $this->locatorMock,
            'productLinkRepository' => $this->productLinkRepository,
            'productRepository' => $this->productRepository,
            'imageHelper' => $this->imageHelper,
            'status' => $this->status,
            'attributeSetRepository' => $this->attributeSetRepository,
        ]);
    }

    /**
     * @return void
     */
    public function testModifyMeta()
    {
        $this->assertArrayHasKey(Related::DATA_SCOPE_RELATED, $this->getModel()->modifyMeta([]));
    }

    /**
     * @return void
     */
    public function testModifyData()
    {
        $data = $this->getSampleData();

        $this->assertSame($data, $this->getModel()->modifyData($data));
    }

    /**
     * @return void
     * @dataProvider sortingDataProvider
     */
    public function testSorting(array $productLinks, array $expectedLinks): void
    {
        $currentProductId = 1;
        $currentStoreId = 1;
        $thumnailUrl = '/path/to/thumnail';
        $model = $this->getModel();
        $priceModifier = $this->createMock(Price::class);
        $attributeSet = $this->createConfiguredMock(AttributeSetInterface::class, ['getAttributeSetName' => 'Default']);
        $this->objectManager->setBackwardCompatibleProperty($model, 'priceModifier', $priceModifier);
        $products = $this->getProducts();
        $priceModifier->method('prepareDataSource')
            ->willReturnArgument(0);
        $this->productMock->method('getId')
            ->willReturn($currentProductId);
        $this->storeMock->method('getId')
            ->willReturn($currentStoreId);
        $this->imageHelper->method('init')
            ->willReturnSelf();
        $this->imageHelper->method('getUrl')
            ->willReturn($thumnailUrl);
        $this->status->method('getOptionText')
            ->willReturn('Enabled');
        $this->attributeSetRepository->method('get')
            ->willReturn($attributeSet);
        $this->productRepository
            ->method('get')
            ->willReturnCallback(
                function (string $sku) use ($products) {
                    return $products[$sku];
                }
            );
        $this->productLinkRepository->method('getList')
            ->willReturn(
                array_map(
                    function (array $linkData) {
                        $link = $this->createPartialMock(Link::class, []);
                        $link->setData($linkData);
                        return $link;
                    },
                    $productLinks
                )
            );
        $data = $this->getSampleData();
        $expected = $data;
        $expected[$currentProductId]['links'] = $expectedLinks;
        $expected[$currentProductId]['product'] = [
            'current_product_id' => $currentProductId,
            'current_store_id' => $currentStoreId,
        ];

        $this->assertSame($expected, $this->getModel()->modifyData($data));
    }

    /**
     * @return ProductInterface[]
     */
    private function getProducts(): array
    {
        $products = [];
        $n = 1;
        do {
            $sku = 'simple-' . $n;
            $product = $this->createMock(ProductInterface::class);
            $product->method('getId')->willReturn($n);
            $product->method('getSku')->willReturn($sku);
            $product->method('getName')->willReturn('Simple ' . $n);
            $product->method('getPrice')->willReturn($n % 10);
            $products[$sku] = $product;
        } while (++$n < 20);
        return $products;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function sortingDataProvider(): array
    {
        return [
            [
                [
                    [
                        'link_type' => Related::DATA_SCOPE_RELATED,
                        'linked_product_sku' => 'simple-3',
                        'position' => 2,
                    ],
                    [
                        'link_type' => Related::DATA_SCOPE_CROSSSELL,
                        'linked_product_sku' => 'simple-6',
                        'position' => 3,
                    ],
                    [
                        'link_type' => Related::DATA_SCOPE_UPSELL,
                        'linked_product_sku' => 'simple-9',
                        'position' => 1,
                    ],
                    [
                        'link_type' => Related::DATA_SCOPE_RELATED,
                        'linked_product_sku' => 'simple-13',
                        'position' => 3,
                    ],
                    [
                        'link_type' => Related::DATA_SCOPE_CROSSSELL,
                        'linked_product_sku' => 'simple-17',
                        'position' => 2,
                    ],
                    [
                        'link_type' => Related::DATA_SCOPE_UPSELL,
                        'linked_product_sku' => 'simple-19',
                        'position' => 2,
                    ],
                    [
                        'link_type' => Related::DATA_SCOPE_RELATED,
                        'linked_product_sku' => 'simple-2',
                        'position' => 1,
                    ],
                    [
                        'link_type' => Related::DATA_SCOPE_CROSSSELL,
                        'linked_product_sku' => 'simple-11',
                        'position' => 1,
                    ],
                    [
                        'link_type' => Related::DATA_SCOPE_UPSELL,
                        'linked_product_sku' => 'simple-7',
                        'position' => 3,
                    ],
                ],
                [
                    Related::DATA_SCOPE_RELATED => [
                        [
                            'id' => 2,
                            'thumbnail' => '/path/to/thumnail',
                            'name' => 'Simple 2',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'simple-2',
                            'price' => 2,
                            'position' => 1,
                        ],
                        [
                            'id' => 3,
                            'thumbnail' => '/path/to/thumnail',
                            'name' => 'Simple 3',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'simple-3',
                            'price' => 3,
                            'position' => 2,
                        ],
                        [
                            'id' => 13,
                            'thumbnail' => '/path/to/thumnail',
                            'name' => 'Simple 13',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'simple-13',
                            'price' => 3,
                            'position' => 3,
                        ],
                    ],
                    Related::DATA_SCOPE_CROSSSELL => [
                        [
                            'id' => 11,
                            'thumbnail' => '/path/to/thumnail',
                            'name' => 'Simple 11',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'simple-11',
                            'price' => 1,
                            'position' => 1,
                        ],
                        [
                            'id' => 17,
                            'thumbnail' => '/path/to/thumnail',
                            'name' => 'Simple 17',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'simple-17',
                            'price' => 7,
                            'position' => 2,
                        ],
                        [
                            'id' => 6,
                            'thumbnail' => '/path/to/thumnail',
                            'name' => 'Simple 6',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'simple-6',
                            'price' => 6,
                            'position' => 3,
                        ],
                    ],
                    Related::DATA_SCOPE_UPSELL => [
                        [
                            'id' => 9,
                            'thumbnail' => '/path/to/thumnail',
                            'name' => 'Simple 9',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'simple-9',
                            'price' => 9,
                            'position' => 1,
                        ],
                        [
                            'id' => 19,
                            'thumbnail' => '/path/to/thumnail',
                            'name' => 'Simple 19',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'simple-19',
                            'price' => 9,
                            'position' => 2,
                        ],
                        [
                            'id' => 7,
                            'thumbnail' => '/path/to/thumnail',
                            'name' => 'Simple 7',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'simple-7',
                            'price' => 7,
                            'position' => 3,
                        ],
                    ],
                ],
            ],
        ];
    }
}
