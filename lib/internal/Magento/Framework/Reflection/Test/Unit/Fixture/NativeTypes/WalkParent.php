<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypes;

abstract class WalkParent
{
    abstract public function getFromParent(): int;

    abstract public function getUnknownParent();

    /**
     * @return string Parent.
     */
    abstract public function getDocumentedParent(): int;
}
