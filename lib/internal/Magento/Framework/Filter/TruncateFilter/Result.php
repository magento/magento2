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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
