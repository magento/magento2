<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

/**
 * Catalog input types that may be used in layered navigation.
 */
class FilterableAllowedInputTypes
{
    /**
     * @var string[]
     */
    private array $inputTypes;

    /**
     * @param string[] $inputTypes
     */
    public function __construct(array $inputTypes = [])
    {
        $this->inputTypes = $inputTypes;
    }

    /**
     * Check whether the catalog input type may be used in layered navigation.
     *
     * @param mixed $frontendInput
     * @return bool
     */
    public function isAllowed(mixed $frontendInput): bool
    {
        return in_array((string)$frontendInput, $this->inputTypes, true);
    }
}
