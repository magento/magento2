<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\ReviewRenderer;

use Magento\Catalog\Block\Product\ReviewRenderer\DefaultProvider;
use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultProviderTest extends TestCase
{
    /**
     * @var DefaultProvider
     */
    private DefaultProvider $model;

    /**
     * @var Product|MockObject
     */
    private Product|MockObject $productMock;

    protected function setUp(): void
    {
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new DefaultProvider();
    }

    /**
     * Test that DefaultProvider implements ReviewRendererInterface
     */
    public function testImplementsReviewRendererInterface()
    {
        $this->assertInstanceOf(ReviewRendererInterface::class, $this->model);
    }

    /**
     * Test with various template types
     *
     * @param string $templateType
     * @param bool $displayIfNoReviews
     * @dataProvider templateTypeDataProvider
     */
    public function testGetReviewsSummaryHtmlReturnsEmptyString($templateType, $displayIfNoReviews)
    {
        $result = $this->model->getReviewsSummaryHtml(
            $this->productMock,
            $templateType,
            $displayIfNoReviews
        );

        $this->assertSame('', $result);
        $this->assertIsString($result);
    }

    /**
     * Data provider for template types
     *
     * @return array
     */
    public static function templateTypeDataProvider(): array
    {
        return [
            'default_view_no_display' => [ReviewRendererInterface::DEFAULT_VIEW, false],
            'default_view_display' => [ReviewRendererInterface::DEFAULT_VIEW, true],
            'short_view_no_display' => [ReviewRendererInterface::SHORT_VIEW, false],
            'full_view_display' => [ReviewRendererInterface::FULL_VIEW, true],
            'custom_template_no_display' => ['custom_template_type', false]
        ];
    }

    /**
     * Test getReviewsSummaryHtml with null product
     */
    public function testGetReviewsSummaryHtmlWithNullProduct()
    {
        $this->expectException(\TypeError::class);

        // Pass null as product to trigger TypeError
        $this->model->getReviewsSummaryHtml(
            null,
            ReviewRendererInterface::DEFAULT_VIEW,
            false
        );
    }

    /**
     * Test getReviewsSummaryHtml with invalid template type
     */
    public function testGetReviewsSummaryHtmlWithInvalidTemplateType()
    {
        // Even with invalid template type, the default provider should return empty string
        $result = $this->model->getReviewsSummaryHtml(
            $this->productMock,
            'invalid_template_type_12345',
            false
        );

        $this->assertSame('', $result);
        $this->assertIsString($result);
    }

    /**
     * Test getReviewsSummaryHtml with null product ID
     */
    public function testGetReviewsSummaryHtmlWithNullProductId()
    {
        // Mock product with null ID
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(null);

        $result = $this->model->getReviewsSummaryHtml(
            $this->productMock,
            ReviewRendererInterface::DEFAULT_VIEW,
            true
        );

        $this->assertSame('', $result);
        $this->assertIsString($result);
    }

    /**
     * Test all template type combinations
     *
     * @param string $templateType
     * @param bool $displayIfNoReviews
     * @dataProvider allTemplateTypeCombinationsDataProvider
     */
    public function testAllTemplateTypeCombinations($templateType, $displayIfNoReviews)
    {
        // Test with product having various IDs
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(123);

        $result = $this->model->getReviewsSummaryHtml(
            $this->productMock,
            $templateType,
            $displayIfNoReviews
        );

        $this->assertSame('', $result);
        $this->assertIsString($result);
    }

    /**
     * Data provider for all template type combinations
     *
     * @return array
     */
    public static function allTemplateTypeCombinationsDataProvider(): array
    {
        return [
            'default_view_true' => [ReviewRendererInterface::DEFAULT_VIEW, true],
            'default_view_false' => [ReviewRendererInterface::DEFAULT_VIEW, false],
            'short_view_true' => [ReviewRendererInterface::SHORT_VIEW, true],
            'short_view_false' => [ReviewRendererInterface::SHORT_VIEW, false],
            'full_view_true' => [ReviewRendererInterface::FULL_VIEW, true],
            'full_view_false' => [ReviewRendererInterface::FULL_VIEW, false],
            'empty_string_true' => ['', true],
            'empty_string_false' => ['', false],
            'custom_type_true' => ['my_custom_template', true],
            'custom_type_false' => ['my_custom_template', false],
            'numeric_type_true' => ['123', true],
            'numeric_type_false' => ['123', false],
        ];
    }
}
