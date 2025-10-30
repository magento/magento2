<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Composite;

/**
 * Adminhtml block for showing product options fieldsets
 *
 * @api
 * @since 100.0.2
 */
class Fieldset extends \Magento\Framework\View\Element\Text\ListText
{
    /**
     *
     * Iterates through fieldsets and fetches complete html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $children = $this->getLayout()->getChildBlocks($this->getNameInLayout());
        $total = count($children);
        $i = 0;
        $this->setText('');
        /** @var $block \Magento\Framework\View\Element\AbstractBlock  */
        foreach ($children as $block) {
            $i++;
            $block->setIsLastFieldset($i == $total);

            $this->addText($block->toHtml());
        }

        return parent::_toHtml();
    }
}
