<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Form Input/Output Strip HTML tags Filter
 */
namespace Magento\Framework\Data\Form\Filter;

class Striptags implements \Magento\Framework\Data\Form\Filter\FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function inputFilter($value)
    {
        return $value !== null ? strip_tags($value) : '';
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
