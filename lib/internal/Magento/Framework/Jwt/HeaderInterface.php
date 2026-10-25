<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * JWT Header.
 */
interface HeaderInterface
{
    /**
     * Parameters.
     *
     * @return HeaderParameterInterface[]
     */
    public function getParameters(): array;

    /**
     * Find a parameter by name.
     *
     * @param string $name
     * @return HeaderParameterInterface|null
     */
    public function getParameter(string $name): ?HeaderParameterInterface;
}
