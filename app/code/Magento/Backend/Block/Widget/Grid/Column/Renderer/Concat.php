<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend grid item renderer concat
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

class Concat extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\Object $row
     * @return  string
     */
    public function render(\Magento\Framework\Object $row)
    {
        $dataArr = [];
        $column = $this->getColumn();
        $methods = $column->getGetter() ?: $column->getIndex();
        foreach ($methods as $method) {
            if ($column->getGetter()
                && is_callable([$row, $method])
                && substr_compare('get', $method, 1, 3) !== 0
            ) {
                $data = call_user_func([$row, $method]);
            } else {
                $data = $row->getData($method);
            }
            if (strlen($data) > 0) {
                $dataArr[] = $data;
            }
        }
        $data = implode($column->getSeparator(), $dataArr);

        return $data;
    }
}
