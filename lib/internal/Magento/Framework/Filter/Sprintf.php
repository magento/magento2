<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Laminas\Filter\FilterInterface;

class Sprintf implements FilterInterface
{
    /**
     * @var string
     */
    protected $format;

    /**
     * @var int
     */
    protected $decimals;

    /**
     * @var string
     */
    protected $decPoint;

    /**
     * @var string
     */
    protected $thousandsSep;

    /**
     * @param string $format
     * @param int|null $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     */
    public function __construct($format, $decimals = null, $decPoint = '.', $thousandsSep = ',')
    {
        $this->format = $format;
        $this->decimals = $decimals;
        $this->decPoint = $decPoint;
        $this->thousandsSep = $thousandsSep;
    }

    /**
     * Returns the result of filtering $value.
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        if (null !== $this->decimals) {
            $value = number_format($value, $this->decimals, $this->decPoint, $this->thousandsSep);
        }
        return sprintf($this->format, $value);
    }
}
