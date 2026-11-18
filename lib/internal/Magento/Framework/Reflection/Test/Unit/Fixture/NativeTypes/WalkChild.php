<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypes;

class WalkChild extends WalkParent implements DocumentedContractInterface, UndocumentedContractInterface
{
    public function getFromInterface(): int
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function getFromUndocumented(): int
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function getUnknown()
    {
        return null;
    }

    /**
     * Get a value.
     */
    public function getUnknownParent()
    {
        return null;
    }

    public function getFromParent(): int
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function getDocumentedParent(): int
    {
        return 1;
    }
}
