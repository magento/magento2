<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Grid column filter interface
 *
 * @api
 * @since 100.0.2
 */
interface FilterInterface
{
    /**
     * Retrieve column related to filter
     *
     * @return Column
     */
    public function getColumn();

    /**
     * Set column related to filter
     *
     * @param Column $column
     * @return AbstractFilter
     */
    public function setColumn($column);

    /**
     * Retrieve filter html
     *
     * @return string
     */
    public function getHtml();
}
