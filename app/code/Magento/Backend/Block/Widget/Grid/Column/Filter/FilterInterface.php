<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     */
    public function getColumn();

    /**
     * @param Column $column
     * @return AbstractFilter
     */
    public function setColumn($column);

    /**
     * @return string
     */
    public function getHtml();
}
