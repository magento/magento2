<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\TruncateFilter;

<<<<<<< HEAD
=======
/**
 * Resulting class for truncate filter
 */
>>>>>>> upstream/2.2-develop
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
     * Result constructor.
=======
>>>>>>> upstream/2.2-develop
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
    public function setValue(string $value) : void
=======
    public function setValue(string $value)
>>>>>>> upstream/2.2-develop
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
    public function setRemainder(string $remainder) : void
=======
    public function setRemainder(string $remainder)
>>>>>>> upstream/2.2-develop
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
