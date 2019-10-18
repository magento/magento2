<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Saving product with linked products
 *
 * @magentoAppArea adminhtml
 */
class LinksTest extends AbstractBackendController
{
    /** @var array */
    private $linkTypes = [
        'upsell',
        'crosssell',
        'related',
    ];

    /** @var ProductRepositoryInterface $productRepository */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * Test add simple related, up-sells, cross-sells product
     *
     * @dataProvider addRelatedUpSellCrossSellProductsProvider
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoDbIsolation enabled
     * @param array $postData
     * @return void
     */
    public function testAddRelatedUpSellCrossSellProducts(array $postData): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product/save');
        $product = $this->productRepository->get('simple');
        $this->assertEquals(
            $this->getExpectedLinks($postData['links']),
            $this->getActualLinks($product),
            "Expected linked products do not match actual linked products!"
        );
    }

    /**
     * Provide test data for testAddRelatedUpSellCrossSellProducts().
     *
     * @return array
     */
    public function addRelatedUpSellCrossSellProductsProvider(): array
    {
        return [
            [
                'post_data' => [
                    'product' => [
                        'attribute_set_id' => '4',
                        'status' => '1',
                        'name' => 'Simple Product',
                        'sku' => 'simple',
                        'url_key' => 'simple-product',
                    ],
                    'links' => [
                        'upsell' => [
                            ['id' => '10'],
                        ],
                        'crosssell' => [
                            ['id' => '11'],
                        ],
                        'related' => [
                            ['id' => '12'],
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Set an array of expected related, up-sells, cross-sells product identifiers
     *
     * @param array $links
     * @return array
     */
    private function getExpectedLinks(array $links): array
    {
        $expectedLinks = [];
        foreach ($this->linkTypes as $linkType) {
            $expectedLinks[$linkType] = [];
            foreach ($links[$linkType] as $productData) {
                $expectedLinks[$linkType][] = $productData['id'];
            }
        }

        return $expectedLinks;
    }

    /**
     * Get an array of received related, up-sells, cross-sells products
     *
     * @param ProductInterface|Product $product
     * @return array
     */
    private function getActualLinks(ProductInterface $product): array
    {
        $actualLinks = [];
        foreach ($this->linkTypes as $linkType) {
            $products = [];
            $actualLinks[$linkType] = [];
            switch ($linkType) {
                case 'upsell':
                    $products = $product->getUpSellProducts();
                    break;
                case 'crosssell':
                    $products = $product->getCrossSellProducts();
                    break;
                case 'related':
                    $products = $product->getRelatedProducts();
                    break;
            }
            /** @var ProductInterface|Product $productItem */
            foreach ($products as $productItem) {
                $actualLinks[$linkType][] = $productItem->getId();
            }
        }

        return $actualLinks;
    }
}
