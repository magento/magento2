<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypes;

interface NativeOnlyInterface
{
    public function getId(): int;

    public function getChild(): \stdClass;

    public function getNullable(): ?\stdClass;

    public function reset(): void;

    public function getSelf(): self;

    public function getStatic(): static;

    public function getWithParameter(int $id, string $name = ''): string;

    public function getUnknown();
}
