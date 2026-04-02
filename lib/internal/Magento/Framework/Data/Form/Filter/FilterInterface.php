<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Form Input/Output Filter Interface
 */
namespace Magento\Framework\Data\Form\Filter;

/**
 * @api
 * @since 100.0.2
 */
interface FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function inputFilter($value);

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function outputFilter($value);
}
