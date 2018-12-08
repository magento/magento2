<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\TruncateFilter;

<<<<<<< HEAD
/**
 * Resulting class for truncate filter
 */
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
=======
     * Result constructor.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
    public function setValue(string $value)
=======
    public function setValue(string $value) : void
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
    public function setRemainder(string $remainder)
=======
    public function setRemainder(string $remainder) : void
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
