<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset;

/**
 * Adminhtml block for fieldset of bundle product
 */
class Bundle extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
{
    /**
     * Returns string with json config for bundle product
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $options = [];
        $optionsArray = $this->getOptions();
        foreach ($optionsArray as $option) {
            $optionId = $option->getId();
            $options[$optionId] = ['id' => $optionId, 'selections' => []];
            foreach ($option->getSelections() as $selection) {
                $options[$optionId]['selections'][$selection->getSelectionId()] = [
                    'can_change_qty' => $selection->getSelectionCanChangeQty(),
                    'default_qty' => $selection->getSelectionQty(),
                ];
            }
        }
        $config = ['options' => $options];
        return $this->jsonEncoder->encode($config);
    }
}
