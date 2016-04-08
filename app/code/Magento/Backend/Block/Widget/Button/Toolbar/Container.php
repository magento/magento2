<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Button\Toolbar;

use Magento\Backend\Block\Widget\Button\ContextInterface;

/**
 * @method \Magento\Backend\Block\Widget\Button\Item getButtonItem()
 * @method ContextInterface getContext()
 * @method ContextInterface setContext(ContextInterface $context)
 */
class Container extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Create button renderer
     *
     * @param string $blockName
     * @param string $blockClassName
     * @return \Magento\Backend\Block\Widget\Button
     */
    protected function createButton($blockName, $blockClassName = null)
    {
        if (null === $blockClassName) {
            $blockClassName = 'Magento\Backend\Block\Widget\Button';
        }
        return $this->getLayout()->createBlock($blockClassName, $blockName);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $item = $this->getButtonItem();
        $context = $this->getContext();

        if ($item && $context && $context->canRender($item)) {
            $data = $item->getData();
            $blockClassName = isset($data['class_name']) ? $data['class_name'] : null;
            $buttonName = $this->getContext()->getNameInLayout() . '-' . $item->getId() . '-button';
            $block = $this->createButton($buttonName, $blockClassName);
            $block->setData($data);
            return $block->toHtml();
        }
        return parent::_toHtml();
    }
}
