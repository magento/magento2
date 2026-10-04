<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product;

use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies escaping strategy for custom attributes in product image templates.
 *
 * @magentoAppArea frontend
 */
class ImageTemplateEscapingTest extends TestCase
{
    /**
     * Ensure custom attribute values are escaped using escapeHtml in image_with_borders.phtml
     */
    public function testCustomAttributeEscapingInImageWithBordersTemplate(): void
    {
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);

        $valueNeedingHtmlEscape = 'http://example.test/media/x.jpg';

        $block = $layout->createBlock(
            Image::class,
            'test_image_with_borders_escape',
            [
                'data' => [
                    'template' => 'Magento_Catalog::product/image_with_borders.phtml',
                    'image_url' => 'http://example.test/media/x.jpg',
                    'width' => 100,
                    'height' => 80,
                    'label' => 'Test',
                    'ratio' => 0.8,
                    'custom_attributes' => [
                        'data-src' => $valueNeedingHtmlEscape,
                    ],
                    'class' => 'product-image-photo',
                    'product_id' => 123,
                ],
            ]
        );

        $html = $block->toHtml();

        $expectedEscaped = htmlspecialchars($valueNeedingHtmlEscape, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
        $this->assertStringContainsString('data-src="' . $expectedEscaped . '"', $html);
    }

    /**
     * Ensure custom attribute values are escaped using escapeHtml in image.phtml
     */
    public function testCustomAttributeEscapingInDeprecatedImageTemplate(): void
    {
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);

        $valueNeedingHtmlEscape = 'http://example.test/media/x.jpg';

        $block = $layout->createBlock(
            Image::class,
            'test_image_escape_deprecated',
            [
                'data' => [
                    'template' => 'Magento_Catalog::product/image.phtml',
                    'image_url' => 'http://example.test/media/y.jpg',
                    'width' => 120,
                    'height' => 90,
                    'label' => 'Test',
                    'ratio' => 0.75,
                    'custom_attributes' => [
                        'data-src' => $valueNeedingHtmlEscape,
                    ],
                    'class' => 'photo image',
                    'product_id' => 456,
                ],
            ]
        );

        $html = $block->toHtml();

        $expectedEscaped = htmlspecialchars($valueNeedingHtmlEscape, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
        $this->assertStringContainsString('data-src="' . $expectedEscaped . '"', $html);
    }
}
