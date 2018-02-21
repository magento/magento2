<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option;

/**
 * Bundle option multi select type renderer
 *
 * @api
 * @since 100.0.2
 */
class Multi extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/view/type/bundle/option/multi.phtml';

    /**
     * @inheritdoc
     * @since 100.2.0
     */
    protected function assignSelection(\Magento\Bundle\Model\Option $option, $selectionId)
    {
        if (is_array($selectionId)) {
            foreach ($selectionId as $id) {
                if ($id && $option->getSelectionById($id)) {
                    $this->_selectedOptions[] = $id;
                }
            }
        } else {
            parent::assignSelection($option, $selectionId);
        }
    }
}
