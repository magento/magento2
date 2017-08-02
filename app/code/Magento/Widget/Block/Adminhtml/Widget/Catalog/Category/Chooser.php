<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Category chooser for widget's layout updates
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Catalog\Category;

/**
 * Class \Magento\Widget\Block\Adminhtml\Widget\Catalog\Category\Chooser
 *
 * @since 2.0.0
 */
class Chooser extends \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser
{
    /**
     * Get JSON of a tree node or an associative array
     *
     * @param \Magento\Framework\Data\Tree\Node|array $node
     * @param int $level
     * @return string
     * @since 2.0.0
     */
    protected function _getNodeJson($node, $level = 0)
    {
        $item = parent::_getNodeJson($node, $level);
        $item['level'] = $node->getLevel();
        return $item;
    }
}
