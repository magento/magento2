<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypes;

interface CompatibilityInterface
{
    /**
     * @param int $id Identifier.
     * @return string Label.
     */
    public function documented($id);

    /**
     * @param int $id Identifier.
     * @return string Label.
     */
    public function matching(int $id): string;

    /**
     * @return \stdClass|null Child.
     */
    public function nullable();

    /**
     * @param string $id Identifier.
     * @return string Label.
     */
    public function disagreeing(int $id): int;

    /**
     * @param string[] $second Second.
     * @param int[] $first First.
     * @return void
     */
    public function reordered(array $first, array $second): void;

    /**
     * @return
     */
    public function emptyReturn(): int;
}
