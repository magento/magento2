<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Integration\Fixture;

interface NativeDataInterface
{
    public function getId(): int;

    public function getName(): ?string;
}
