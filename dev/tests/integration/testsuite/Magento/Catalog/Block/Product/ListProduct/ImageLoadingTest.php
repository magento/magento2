<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\ListProduct;

use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Checks the loading priority of product images in the product listing.
 */
#[AppArea('frontend')]
class ImageLoadingTest extends TestCase
{
    #[
        AppIsolation(true),
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(ProductFixture::class, as: 'product3'),
    ]
    public function testOnlyFirstListingImageIsEagerWithHighPriority(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $registry = $objectManager->get(Registry::class);
        $registry->unregister('current_category');
        $fixtures = DataFixtureStorageManager::getStorage();
        $registry->register('current_category', $fixtures->get('category'));
        $page = $objectManager->get(PageFactory::class)->create();
        $page->addHandle(['default', 'catalog_category_view']);
        $page->getLayout()->generateXml();
        /** @var ListProduct $block */
        $block = $page->getLayout()->getBlock('category.products.list');
        $block->setCollection(
            $objectManager->get(CollectionFactory::class)->create()
                ->addAttributeToSelect(['name', 'small_image'])
                ->addAttributeToFilter('sku', [
                    'in' => [
                        $fixtures->get('product1')->getSku(),
                        $fixtures->get('product2')->getSku(),
                        $fixtures->get('product3')->getSku(),
                    ],
                ])
        );

        $images = $this->getProductImageTags($block->toHtml());

        $this->assertCount(3, $images);
        $this->assertSame(['eager'], $this->getAttributeValues($images[0], 'loading'));
        $this->assertSame(['high'], $this->getAttributeValues($images[0], 'fetchpriority'));
        foreach (array_slice($images, 1) as $image) {
            $this->assertSame(['lazy'], $this->getAttributeValues($image, 'loading'));
            $this->assertSame([], $this->getAttributeValues($image, 'fetchpriority'));
        }
    }

    /**
     * @param string $template
     * @param array $customAttributes
     * @param string $expectedLoading
     */
    #[DataProvider('imageTemplateProvider')]
    public function testImageTemplateEmitsOneLoadingAttribute(
        string $template,
        array $customAttributes,
        string $expectedLoading
    ): void {
        $block = Bootstrap::getObjectManager()->get(LayoutInterface::class)->createBlock(
            Image::class,
            '',
            [
                'data' => [
                    'template' => $template,
                    'image_url' => 'http://example.test/media/image.jpg',
                    'width' => 100,
                    'height' => 80,
                    'label' => 'Test',
                    'ratio' => 0.8,
                    'custom_attributes' => $customAttributes,
                    'class' => 'product-image-photo',
                    'product_id' => 1,
                ],
            ]
        );

        $images = $this->getProductImageTags($block->toHtml());

        $this->assertCount(1, $images);
        $this->assertSame([$expectedLoading], $this->getAttributeValues($images[0], 'loading'));
    }

    /**
     * @return array
     */
    public static function imageTemplateProvider(): array
    {
        return [
            'image_with_borders default' => ['Magento_Catalog::product/image_with_borders.phtml', [], 'lazy'],
            'image_with_borders eager' => [
                'Magento_Catalog::product/image_with_borders.phtml',
                ['loading' => 'eager'],
                'eager',
            ],
            'image default' => ['Magento_Catalog::product/image.phtml', [], 'lazy'],
            'image eager' => ['Magento_Catalog::product/image.phtml', ['loading' => 'eager'], 'eager'],
        ];
    }

    /**
     * @param string $html
     * @return string[]
     */
    private function getProductImageTags(string $html): array
    {
        preg_match_all('/<img\b[^>]*\bproduct-image-photo\b[^>]*>/', $html, $matches);

        return $matches[0];
    }

    /**
     * @param string $tag
     * @param string $attribute
     * @return string[]
     */
    private function getAttributeValues(string $tag, string $attribute): array
    {
        preg_match_all('/\s' . preg_quote($attribute, '/') . '="([^"]*)"/', $tag, $matches);

        return $matches[1];
    }
}
