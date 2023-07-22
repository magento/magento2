<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Catalog\Category;

use Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser as WidgetChooser;
use Magento\Framework\Data\Tree\Node;

/**
 * Category chooser for widget's layout updates
 */
class Chooser extends WidgetChooser
{
    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Node|array $node
     * @param int $level
     * @return array
     */
    protected function _getNodeJson($node, $level = 0)
    {
        $item = parent::_getNodeJson($node, $level);
        $item['level'] = $node->getLevel();
        return $item;
    }
}
