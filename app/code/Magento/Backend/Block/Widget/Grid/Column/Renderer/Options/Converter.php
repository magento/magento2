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
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer\Options;

class Converter
{
    /**
     * Convert data from tree format to flat format
     *
     * @param array $treeData
     * @return array
     */
    public function toFlatArray($treeData)
    {
        $options = array();
        if (is_array($treeData)) {
            foreach ($treeData as $item) {
                if (isset($item['value']) && isset($item['label'])) {
                    $options[$item['value']] = $item['label'];
                }
            }
        }
        return $options;
    }

    /**
     * Convert data from flat format to tree format
     *
     * @param array $flatData
     * @return array
     */
    public function toTreeArray($flatData)
    {
        $options = array();
        if (is_array($flatData)) {
            foreach ($flatData as $key => $item) {
                $options[] = array('value' => $key, 'label' => $item);
            }
        }
        return $options;
    }
}
