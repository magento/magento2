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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    /**
     * @var RendererInterface|MockObject
     */
    private $rendererMock;

    protected function setUp(): void
    {
        $this->model = new AddProductsToCartError(self::ERROR_MESSAGE_CODES_MAPPER);
        $this->defaultRenderer = Phrase::getRenderer();
        $this->rendererMock = $this->createMock(RendererInterface::class);
        Phrase::setRenderer($this->rendererMock);
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
        $this->rendererMock->method('render')->willReturn($translatedMessage);

        $phrase = new Phrase('Could not find a product with SKU "%sku"', ['sku' => 'ABC']);
        $error = $this->model->create($phrase, 1);

        // client-facing message stays translated ...
        $this->assertSame($translatedMessage, $error->getMessage());
        // ... but the technical code is derived from the untranslated text
        $this->assertSame('PRODUCT_NOT_FOUND', $error->getCode());
        $this->assertSame(1, $error->getCartItemPosition());
    }

    /**
     * A plain string message keeps the previous behavior: no rendering, code resolved from the string.
     */
    public function testCreateWithStringMessageKeepsBackwardCompatibility(): void
    {
        $this->rendererMock->expects($this->never())->method('render');

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
}
