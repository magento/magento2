<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
