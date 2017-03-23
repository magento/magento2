<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

class Sprintf implements \Zend_Filter_Interface
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
     * @param null|int $decimals
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
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        if (null !== $this->decimals) {
            $value = number_format($value, $this->decimals, $this->decPoint, $this->thousandsSep);
        }
        $value = sprintf($this->format, $value);
        return $value;
    }
}
