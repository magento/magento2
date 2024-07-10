<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;

/**
 * Class for product url rewrites tests
 *
 */
class ProductUrlRewriteVisibilityTest extends AbstractUrlRewriteTest
{
    private const URL_KEY_EMPTY_MESSAGE = 'Failed asserting URL key is empty for the given product';

    /** @var string */
    private $suffix;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->suffix = $this->config->getValue(
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @dataProvider invisibleProductDataProvider
     * @param array $expectedData
     * @return void
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple', 'name' => 'Simple Url Test Product',
            'visibility' => Visibility::VISIBILITY_NOT_VISIBLE]),
    ]
    public function testUrlRewriteOnInvisibleProductEdit(array $expectedData): void
    {
        $product = $this->productRepository->get('simple', true, 0, true);
        $this->assertUrlKeyEmpty($product, self::URL_KEY_EMPTY_MESSAGE);

        //Update visibility and check the database entry
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $product = $this->productRepository->save($product);

        $productUrlRewriteCollection = $this->getEntityRewriteCollection($product->getId());
        $this->assertRewrites(
            $productUrlRewriteCollection,
            $this->prepareData($expectedData, (int)$product->getId())
        );

        //Update visibility and check if the entry is removed from the database
        $product = $this->productRepository->get('simple', true, 0, true);
        $product->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
        $product = $this->productRepository->save($product);

        $this->assertUrlKeyEmpty($product, self::URL_KEY_EMPTY_MESSAGE);
    }

    /**
     * @return array
     */
    public function invisibleProductDataProvider(): array
    {
        return [
            [
                'expected_data' => [
                    [
                        'request_path' => 'simple-url-test-product%suffix%',
                        'target_path' => 'catalog/product/view/id/%id%',
                    ],
                ],
            ],
        ];
    }

    /**
     * Assert URL key is empty in database for the given product
     *
     * @param $product
     * @param string $message
     *
     * @return void
     */
    public function assertUrlKeyEmpty($product, $message = ''): void
    {
        $productUrlRewriteItems = $this->getEntityRewriteCollection($product->getId())->getItems();
        $this->assertEmpty($productUrlRewriteItems, $message);
    }

    /**
     * @inheritdoc
     */
    protected function getUrlSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @inheritdoc
     */
    protected function getEntityType(): string
    {
        return DataProductUrlRewriteDatabaseMap::ENTITY_TYPE;
    }
}
