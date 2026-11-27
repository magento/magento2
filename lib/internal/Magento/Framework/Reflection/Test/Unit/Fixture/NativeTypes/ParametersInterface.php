<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypes;

interface ParametersInterface
{
    public function union(int|string $value): void;

    public function arrayWithoutTag(array $items): void;

    /**
     * @param int $id Identifier.
     */
    public function arrayMissingTag(int $id, array $items): void;

    public function untyped($value);

    /**
     * @param Foo[] $items Items text
     */
    public function save(int $id, array $items): void;

    /**
     * @param Foo[] $a First items.
     * @param Bar[] $b Second items.
     */
    public function partialArrays(int $x, array $a, array $b): void;

    /**
     * @param Foo[]
     */
    public function unnamedTag(array $items, int $flag): void;

    /**
     * @param Foo[] $unknown Unknown items.
     */
    public function unknownTag(array $items, int $flag): void;

    /**
     * @param Foo[] $flag Flag text.
     */
    public function otherParameterTag(array $items, int $flag): void;

    /**
     * @param int $id Identifier.
     */
    public function incomplete(int $id, string $name): void;
}
