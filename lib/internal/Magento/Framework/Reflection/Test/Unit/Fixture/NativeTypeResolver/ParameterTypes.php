<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypeResolver;

/**
 * Native parameter declarations for resolver tests.
 */
abstract class ParameterTypes
{
    /**
     * Declare the untypedParameter parameter type.
     *
     * @param mixed $value
     * @return void
     */
    abstract public function untypedParameter($value): void;

    /**
     * Declare the builtinParameter parameter type.
     *
     * @param int $value
     * @return void
     */
    abstract public function builtinParameter(int $value): void;

    /**
     * Declare the classParameter parameter type.
     *
     * @param \DateTime $value
     * @return void
     */
    abstract public function classParameter(\DateTime $value): void;

    /**
     * Declare the nullableParameter parameter type.
     *
     * @param ?int $value
     * @return void
     */
    abstract public function nullableParameter(?int $value): void;

    /**
     * Declare the unionParameter parameter type.
     *
     * @param int|string $value
     * @return void
     */
    abstract public function unionParameter(int|string $value): void;

    /**
     * Declare the intersectionParameter parameter type.
     *
     * @param \Countable&\Stringable $value
     * @return void
     */
    abstract public function intersectionParameter(\Countable&\Stringable $value): void;

    /**
     * Declare the dnfParameter parameter type.
     *
     * @param (\Countable&\Stringable)|\stdClass $value
     * @return void
     */
    abstract public function dnfParameter((\Countable&\Stringable)|\stdClass $value): void;
}
