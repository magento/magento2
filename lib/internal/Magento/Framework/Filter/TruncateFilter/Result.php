<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\TruncateFilter;

/**
 * Resulting class for truncate filter
 */
class Result
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $remainder;

    /**
     * @param string $value
     * @param string $remainder
     */
    public function __construct(string $value, string $remainder)
    {
        $this->value = $value;
        $this->remainder = $remainder;
    }

    /**
     * Set result value
     *
     * @param string $value
     * @return void
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }

    /**
     * Set remainder
     *
     * @param string $remainder
     * @return void
     */
    public function setRemainder(string $remainder)
    {
        $this->remainder = $remainder;
    }

    /**
     * Get remainder
     *
     * @return string
     */
    public function getRemainder() : string
    {
        return $this->remainder;
    }
}
