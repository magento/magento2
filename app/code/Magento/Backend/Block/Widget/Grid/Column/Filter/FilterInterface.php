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
 * @since 100.0.2
 */
interface FilterInterface
{
    /**
     * @return Column
     * @api
     */
    public function getColumn();

    /**
     * @param Column $column
     * @return AbstractFilter
     * @api
     */
    public function setColumn($column);

    /**
     * @return string
     * @api
     */
    public function getHtml();
}
