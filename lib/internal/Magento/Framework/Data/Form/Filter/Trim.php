<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Form Input/Output Trim Filter
 */
namespace Magento\Framework\Data\Form\Filter;

class Trim implements \Magento\Framework\Data\Form\Filter\FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function inputFilter($value)
    {
        return $value !== null ? trim($value, ' ') : '';
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function outputFilter($value)
    {
        return $value;
    }
}
