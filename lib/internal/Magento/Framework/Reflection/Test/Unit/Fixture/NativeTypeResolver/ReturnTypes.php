<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypeResolver;

/**
 * Native type declarations for resolver tests.
 */
abstract class ReturnTypes extends \stdClass
{
    /**
     * Declare the integer return type.
     *
     * @return int
     */
    abstract public function integer(): int;

    /**
     * Declare the string return type.
     *
     * @return string
     */
    abstract public function string(): string;

    /**
     * Declare the boolean return type.
     *
     * @return bool
     */
    abstract public function boolean(): bool;

    /**
     * Declare the float return type.
     *
     * @return float
     */
    abstract public function float(): float;

    /**
     * Declare the nullableInteger return type.
     *
     * @return ?int
     */
    abstract public function nullableInteger(): ?int;

    /**
     * Declare the nullableString return type.
     *
     * @return ?string
     */
    abstract public function nullableString(): ?string;

    /**
     * Declare the nullableBoolean return type.
     *
     * @return ?bool
     */
    abstract public function nullableBoolean(): ?bool;

    /**
     * Declare the nullableFloat return type.
     *
     * @return ?float
     */
    abstract public function nullableFloat(): ?float;

    /**
     * Declare the nullUnion return type.
     *
     * @return int|null
     */
    abstract public function nullUnion(): int|null;

    /**
     * Declare the className return type.
     *
     * @return \DateTime
     */
    abstract public function className(): \DateTime;

    /**
     * Declare the nullableClass return type.
     *
     * @return ?\DateTime
     */
    abstract public function nullableClass(): ?\DateTime;

    /**
     * Declare a return type whose class must remain unloaded.
     *
     * @return NotLoadedReturn
     */
    abstract public function notLoadedClass(): NotLoadedReturn;

    /**
     * Declare a nullable return type whose class must remain unloaded.
     *
     * @return NotLoadedReturn|null
     */
    abstract public function nullableNotLoadedClass(): ?NotLoadedReturn;

    /**
     * Declare the interfaceName return type.
     *
     * @return \DateTimeInterface
     */
    abstract public function interfaceName(): \DateTimeInterface;

    /**
     * Declare the nullableInterface return type.
     *
     * @return ?\DateTimeInterface
     */
    abstract public function nullableInterface(): ?\DateTimeInterface;

    /**
     * Declare the enumName return type.
     *
     * @return Status
     */
    abstract public function enumName(): Status;

    /**
     * Declare the nullableEnum return type.
     *
     * @return ?Status
     */
    abstract public function nullableEnum(): ?Status;

    /**
     * Declare the selfName return type.
     *
     * @return self
     */
    abstract public function selfName(): self;

    /**
     * Declare the nullableSelf return type.
     *
     * @return ?self
     */
    abstract public function nullableSelf(): ?self;

    /**
     * Declare the staticName return type.
     *
     * @return static
     */
    abstract public function staticName(): static;

    /**
     * Declare the nullableStatic return type.
     *
     * @return ?static
     */
    abstract public function nullableStatic(): ?static;

    /**
     * Declare the parentName return type.
     *
     * @return parent
     */
    abstract public function parentName(): parent;

    /**
     * Declare the nullableParent return type.
     *
     * @return ?parent
     */
    abstract public function nullableParent(): ?parent;

    /**
     * Declare the mixedType return type.
     *
     * @return mixed
     */
    abstract public function mixedType(): mixed;

    /**
     * Declare the voidType return type.
     *
     * @return void
     */
    abstract public function voidType(): void;

    /**
     * Declare the arrayType return type.
     *
     * @return array
     */
    abstract public function arrayType(): array;

    /**
     * Declare the nullableArray return type.
     *
     * @return ?array
     */
    abstract public function nullableArray(): ?array;

    /**
     * Declare the iterableType return type.
     *
     * @return iterable
     */
    abstract public function iterableType(): iterable;

    /**
     * Declare the nullableIterable return type.
     *
     * @return ?iterable
     */
    abstract public function nullableIterable(): ?iterable;

    /**
     * Declare the unionType return type.
     *
     * @return int|string
     */
    abstract public function unionType(): int|string;

    /**
     * Declare the nullableUnion return type.
     *
     * @return int|string|null
     */
    abstract public function nullableUnion(): int|string|null;

    /**
     * Declare the intersectionType return type.
     *
     * @return \Countable&\Stringable
     */
    abstract public function intersectionType(): \Countable&\Stringable;

    /**
     * Declare the dnfType return type.
     *
     * @return (\Countable&\Stringable)|\stdClass
     */
    abstract public function dnfType(): (\Countable&\Stringable)|\stdClass;

    /**
     * Declare the neverType return type.
     *
     * @return never
     */
    abstract public function neverType(): never;

    /**
     * Declare the objectType return type.
     *
     * @return object
     */
    abstract public function objectType(): object;

    /**
     * Declare the callableType return type.
     *
     * @return callable
     */
    abstract public function callableType(): callable;

    /**
     * Declare a method without a return type.
     *
     * @return mixed
     */
    abstract public function untyped();

    /**
     * Declare required and optional parameters.
     *
     * @param int $first
     * @param string $second
     * @param bool $optional
     * @return int
     */
    abstract public function withParameters(int $first, string $second, bool $optional = false): int;
}
