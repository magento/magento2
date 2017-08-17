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
     */
    protected function _getSelectedOptions()
    {
        if ($this->_selectedOptions === null) {
            $this->_selectedOptions = [];
            /** @var \Magento\Bundle\Model\Option $option */
            $option = $this->getOption();
            if ($this->getProduct()->hasPreconfiguredValues()) {
                $selectionIds = $this->getProduct()->getPreconfiguredValues()->getData(
                    'bundle_option/' . $option->getId()
                );
                foreach ($selectionIds as $selectionId) {
                    if ($selectionId && $option->getSelectionById($selectionId)) {
                        $this->_selectedOptions[] = $selectionId;
                    }
                }
            }
        }

        return $this->_selectedOptions;
    }
}
