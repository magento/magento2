<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
