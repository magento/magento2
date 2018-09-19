<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer concat
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Concat extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
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
