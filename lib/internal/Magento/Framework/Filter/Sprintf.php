<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Class \Magento\Framework\Filter\Sprintf
 *
 * @since 2.0.0
 */
class Sprintf implements \Zend_Filter_Interface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $format;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $decimals;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $decPoint;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $thousandsSep;

    /**
     * @param string $format
     * @param null|int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @since 2.0.0
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
     * @since 2.0.0
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
