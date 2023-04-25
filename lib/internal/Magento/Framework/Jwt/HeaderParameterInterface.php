<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * JWT Header parameter.
 */
interface HeaderParameterInterface
{
    public const CLASS_REGISTERED = 0;

    public const CLASS_PUBLIC = 1;

    public const CLASS_PRIVATE = 2;

    /**
     * Header parameter's name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Header parameter value.
     *
     * @return int|float|string|bool
     */
    public function getValue();

    /**
     * Parameter's class if possible to identify.
     *
     * @return int|null
     */
    public function getClass(): ?int;
}
