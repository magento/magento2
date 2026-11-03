<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Quote\Model\Cart\AddProductsToCartError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AddProductsToCartErrorTest extends TestCase
{
    private const ERROR_MESSAGE_CODES_MAPPER = [
        'Could not find a product with SKU' => 'PRODUCT_NOT_FOUND',
        'Product that you are trying to add is not available.' => 'NOT_SALABLE',
        'This product is out of stock' => 'INSUFFICIENT_STOCK',
        'Only %s of %s available' => 'INSUFFICIENT_STOCK',
    ];

    /**
     * @var AddProductsToCartError
     */
    private AddProductsToCartError $model;

    /**
     * @var RendererInterface
     */
    private RendererInterface $defaultRenderer;

    protected function setUp(): void
    {
        $this->model = new AddProductsToCartError(self::ERROR_MESSAGE_CODES_MAPPER);
        $this->defaultRenderer = Phrase::getRenderer();
    }

    protected function tearDown(): void
    {
        Phrase::setRenderer($this->defaultRenderer);
    }

    /**
     * The error code must be resolved from the original phrase text, so it stays stable even when
     * the message shown to the client is translated into a non-English locale.
     */
    public function testCreateResolvesCodeFromUntranslatedPhrase(): void
    {
        $translatedMessage = 'Produit introuvable pour le SKU "ABC"';
        $renderer = $this->createStub(RendererInterface::class);
        $renderer->method('render')->willReturn($translatedMessage);
        Phrase::setRenderer($renderer);

        $phrase = new Phrase('Could not find a product with SKU "%sku"', ['sku' => 'ABC']);
        $error = $this->model->create($phrase, 1);

        // client-facing message stays translated ...
        $this->assertSame($translatedMessage, $error->getMessage());
        // ... but the technical code is derived from the untranslated text
        $this->assertSame('PRODUCT_NOT_FOUND', $error->getCode());
        $this->assertSame(1, $error->getCartItemPosition());
    }

    /**
     * A plain string message keeps the previous behavior: the code is resolved from the string.
     */
    public function testCreateWithStringMessageKeepsBackwardCompatibility(): void
    {
        $error = $this->model->create('Product that you are trying to add is not available.', 2);

        $this->assertSame('Product that you are trying to add is not available.', $error->getMessage());
        $this->assertSame('NOT_SALABLE', $error->getCode());
        $this->assertSame(2, $error->getCartItemPosition());
    }

    /**
     * Numeric values in a rendered message are still normalized before the code lookup.
     */
    public function testCreateNormalizesNumbersForCodeLookup(): void
    {
        $error = $this->model->create('Only 5 of 3 available', 0, 3.0);

        $this->assertSame('INSUFFICIENT_STOCK', $error->getCode());
    }

    /**
     * An unmapped message falls back to the UNDEFINED code.
     */
    public function testCreateFallsBackToUndefinedCode(): void
    {
        $error = $this->model->create('Some message with no mapping', 0);

        $this->assertSame('UNDEFINED', $error->getCode());
    }

    /**
     * Cart validation messages are resolved to a specific code by the mapping configured in di.xml.
     *
     * @param string|Phrase $message
     * @param string $expectedCode
     */
    #[DataProvider('configuredMessageCodesDataProvider')]
    public function testCreateResolvesCodeWithConfiguredMapping(string|Phrase $message, string $expectedCode): void
    {
        $model = new AddProductsToCartError($this->getConfiguredErrorMessageCodesMapper());
        $this->assertSame($expectedCode, $model->create($message)->getCode());
    }

    /**
     * @return array
     */
    public static function configuredMessageCodesDataProvider(): array
    {
        return [
            'max sale qty exceeded' => [
                new Phrase('The requested qty exceeds the maximum qty allowed in shopping cart'),
                'INSUFFICIENT_STOCK',
            ],
            'requested qty not available (with dot)' => [
                new Phrase('The requested qty. is not available'),
                'INSUFFICIENT_STOCK',
            ],
            'requested qty not available (without dot)' => [
                'The requested qty is not available',
                'INSUFFICIENT_STOCK',
            ],
            'min sale qty' => [
                new Phrase('The fewest you may purchase is %1.', [2]),
                'INSUFFICIENT_STOCK',
            ],
            'not enough items for sale' => [
                new Phrase('Not enough items for sale'),
                'INSUFFICIENT_STOCK',
            ],
            'only x of y available (rendered)' => [
                'Only 5 of 6 available',
                'INSUFFICIENT_STOCK',
            ],
            'non positive quantity' => [
                new Phrase('The product quantity should be greater than 0'),
                'INVALID_PARAMETER_VALUE',
            ],
            'decimal quantity' => [
                new Phrase('You cannot use decimal quantity for this product.'),
                'INVALID_PARAMETER_VALUE',
            ],
            'qty increments' => [
                new Phrase('You can buy this product only in quantities of %1 at a time.', [3]),
                'INVALID_PARAMETER_VALUE',
            ],
            'qty increments with product name (rendered)' => [
                'You can buy Simple Product 1 only in quantities of 3 at a time.',
                'INVALID_PARAMETER_VALUE',
            ],
            'wrong option data format' => [
                new Phrase('Wrong format of the entered option data'),
                'INVALID_PARAMETER_VALUE',
            ],
            'product not found' => [
                new Phrase('Could not find a product with SKU "%sku"', ['sku' => 'simple']),
                'PRODUCT_NOT_FOUND',
            ],
            'required options missing' => [
                new Phrase('You need to choose options for your item.'),
                'REQUIRED_PARAMETER_MISSING',
            ],
        ];
    }

    /**
     * Read the errorMessageCodesMapper argument configured for AddProductsToCartError in Magento_Quote di.xml
     *
     * @return array
     */
    private function getConfiguredErrorMessageCodesMapper(): array
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . '/../../../../etc/di.xml');
        $xpath = new \DOMXPath($dom);
        $items = $xpath->query(
            '//type[@name="Magento\Quote\Model\Cart\AddProductsToCartError"]'
            . '/arguments/argument[@name="errorMessageCodesMapper"]/item'
        );

        $mapper = [];
        foreach ($items as $item) {
            $mapper[$item->getAttribute('name')] = trim($item->textContent);
        }
        $this->assertNotEmpty($mapper, 'errorMessageCodesMapper is not configured in Magento_Quote di.xml');

        return $mapper;
    }
}
