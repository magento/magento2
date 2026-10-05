<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Webapi;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Webapi\CartItemQtyValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CartItemQtyValidatorTest extends TestCase
{
    #[DataProvider('invalidQtyProvider')]
    public function testRejectsInvalidCartItemQuantity($qty): void
    {
        $validator = new CartItemQtyValidator();

        $this->expectException(InputException::class);
        $validator->validateEntityValue($this->createStub(CartItemInterface::class), 'qty', $qty);
    }

    public static function invalidQtyProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-2],
            'non-numeric' => ['invalid'],
        ];
    }

    #[DataProvider('validQtyProvider')]
    public function testAcceptsPositiveCartItemQuantity($qty): void
    {
        $validator = new CartItemQtyValidator();
        $validator->validateEntityValue($this->createStub(CartItemInterface::class), 'qty', $qty);

        $this->expectNotToPerformAssertions();
    }

    public static function validQtyProvider(): array
    {
        return [
            'integer' => [1],
            'decimal' => [2.5],
        ];
    }

    public function testOtherInputIsNotAffected(): void
    {
        $validator = new CartItemQtyValidator();
        $validator->validateEntityValue($this->createStub(CartItemInterface::class), 'sku', 0);
        $validator->validateEntityValue(new \stdClass(), 'qty', 0);
        $validator->validateComplexArrayType(CartItemInterface::class, []);

        $this->expectNotToPerformAssertions();
    }
}
