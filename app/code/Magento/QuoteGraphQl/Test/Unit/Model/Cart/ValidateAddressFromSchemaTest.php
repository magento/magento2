<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Cart;

use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\StringType;
use Magento\Framework\GraphQl\Schema\Type\NonNull;
use Magento\Framework\GraphQl\Schema\Type\TypeRegistry;
use Magento\Framework\GraphQl\Schema\Type\Input\InputObjectType;
use Magento\QuoteGraphQl\Model\Cart\ValidateAddressFromSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see ValidateAddressFromSchema
 */
class ValidateAddressFromSchemaTest extends TestCase
{
    /** @var ValidateAddressFromSchema */
    private ValidateAddressFromSchema $model;

    /** @var TypeRegistry|MockObject */
    private TypeRegistry $typeRegistry;

    /** @var InputObjectType|MockObject */
    private InputObjectType $cartAddressInputType;

    protected function setUp(): void
    {
        $this->typeRegistry = $this->createMock(TypeRegistry::class);
        $this->cartAddressInputType = $this->createMock(InputObjectType::class);
        $this->typeRegistry->method('get')
            ->with('CartAddressInput')
            ->willReturn($this->cartAddressInputType);
        $this->model = new ValidateAddressFromSchema($this->typeRegistry);
    }

    /**
     * Address missing a nullable field passes validation.
     *
     * Covers the regression from ACP2E-4223 where CartAddressInput.telephone was changed
     * to String! (NonNull), forcing telephone even when the store configures it as Optional.
     * After the fix telephone is String (nullable) and this test must pass.
     */
    public function testAddressWithoutNullableFieldPassesValidation(): void
    {
        $firstnameField = $this->makeField('firstname', true);
        $telephoneField = $this->makeField('telephone', false);

        $this->cartAddressInputType->method('getFields')
            ->willReturn(['firstname' => $firstnameField, 'telephone' => $telephoneField]);

        // telephone absent entirely
        $this->assertTrue($this->model->execute(['firstname' => 'John']));

        // telephone key present but null — nullable, so still valid
        $this->assertTrue($this->model->execute(['firstname' => 'John', 'telephone' => null]));
    }

    /**
     * Address with a NonNull field present and filled passes validation.
     */
    public function testAddressWithAllNonNullFieldsPresentPassesValidation(): void
    {
        $firstnameField = $this->makeField('firstname', true);
        $telephoneField = $this->makeField('telephone', false);

        $this->cartAddressInputType->method('getFields')
            ->willReturn(['firstname' => $firstnameField, 'telephone' => $telephoneField]);

        $this->assertTrue($this->model->execute([
            'firstname' => 'John',
            'telephone' => '+15005550006',
        ]));
    }

    /**
     * Address with a NonNull field key present but set to null fails validation.
     */
    public function testAddressWithNonNullFieldSetToNullFailsValidation(): void
    {
        $firstnameField = $this->makeField('firstname', true);

        $this->cartAddressInputType->method('getFields')
            ->willReturn(['firstname' => $firstnameField]);

        // Key exists, value is null → invalid for NonNull field
        $this->assertFalse($this->model->execute(['firstname' => null]));
    }

    /**
     * Address missing a NonNull field key entirely still passes (output validator, not input).
     *
     * ValidateAddressFromSchema is used on the output side to decide whether a saved address
     * is complete enough to return. A key simply not being present is allowed; only an
     * explicitly-null value for a NonNull field is rejected.
     */
    public function testAddressWithMissingNonNullFieldKeyPassesValidation(): void
    {
        $firstnameField = $this->makeField('firstname', true);

        $this->cartAddressInputType->method('getFields')
            ->willReturn(['firstname' => $firstnameField]);

        // Key does not exist at all → validator treats as acceptable (saved address may be partial)
        $this->assertTrue($this->model->execute([]));
    }

    /**
     * Build a mock InputObjectField with a name and a nullable/NonNull type.
     */
    private function makeField(string $name, bool $isNonNull): InputObjectField
    {
        $field = $this->getMockBuilder(InputObjectField::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getType'])
            ->getMock();
        $field->name = $name;

        if ($isNonNull) {
            $type = $this->getMockBuilder(NonNull::class)
                ->disableOriginalConstructor()
                ->getMock();
        } else {
            $type = $this->createMock(StringType::class);
        }

        $field->method('getType')->willReturn($type);

        return $field;
    }
}
