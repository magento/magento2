<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Grid column filter interface
 *
 * @api
 * @since 2.0.0
 */
interface FilterInterface
{
    /**
     * @return Column
     * @api
     * @since 2.0.0
     */
    public function getColumn();

    /**
     * @param Column $column
     * @return AbstractFilter
     * @api
     * @since 2.0.0
     */
    public function setColumn($column);

    /**
     * @return string
     * @api
     * @since 2.0.0
     */
    public function getHtml();
}
