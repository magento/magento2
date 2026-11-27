<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\Test\Unit\Generator\_files;

/**
 * Source signatures for sensitive parameter generation.
 */
class SensitiveParameterSample
{
    /**
     * Exercise constructor parameter reflection.
     */
    public function __construct(string $label, #[\SensitiveParameter] string $password)
    {
        $this->authenticate($password, $label);
    }

    /**
     * Keep an ordinary parameter next to a sensitive parameter.
     */
    public function authenticate(#[\SensitiveParameter] string $password, string $label): string
    {
        return $password . $label;
    }

    /**
     * Exercise nullable parameters with a default value.
     */
    public function nullable(string $label, #[\SensitiveParameter] ?string $password = null): string
    {
        return $password ?? $label;
    }

    /**
     * Exercise parameters passed by reference.
     */
    public function byReference(string $label, #[\SensitiveParameter] string &$password): void
    {
        $password = $label;
    }

    /**
     * Exercise variadic parameters.
     *
     * @return string[]
     */
    public function variadic(string $label, #[\SensitiveParameter] string ...$passwords): array
    {
        return [$label, ...$passwords];
    }
}
