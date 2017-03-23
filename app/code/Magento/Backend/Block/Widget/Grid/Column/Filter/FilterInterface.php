<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Grid column filter interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
