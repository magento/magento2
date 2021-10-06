<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator\EntityArrayValidator;

/**
 * Value of the size limit of the input array for service input validator
 */
class InputArraySizeLimitValue
{
    /**
     * @var int|null
     */
    private $value;

    /**
     * Set value of input array size limit
     *
     * @param int|null $value
     */
    public function set(?int $value): void
    {
        $this->value = $value;
    }

    /**
     * Get value of input array size limit
     *
     * @return int|null
     */
    public function get(): ?int
    {
        return $this->value;
    }
}
