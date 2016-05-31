<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Control;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\UiComponent\Control\ControlInterface;

/**
 * Class Container
 */
class Container extends AbstractBlock
{
    /**
     * Default button class
     */
    const DEFAULT_CONTROL = 'Magento\Ui\Component\Control\Button';
    const SPLIT_BUTTON = 'Magento\Ui\Component\Control\SplitButton';

    /**
     * Create button renderer
     *
     * @param string $blockName
     * @param string $blockClassName
     * @return ControlInterface
     */
    protected function createButton($blockName, $blockClassName = null)
    {
        if (null === $blockClassName) {
            $blockClassName = static::DEFAULT_CONTROL;
        }

        return $this->getLayout()->createBlock($blockClassName, $blockName);
    }

    /**
     * Render element HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Item $item */
        $item = $this->getButtonItem();
        $data = $item->getData();

        $contextPrefixName = $this->getData('context') ? ($this->getData('context')->getNameInLayout() . '-') : '';
        $block = $this->createButton(
            $contextPrefixName . $item->getId() . '-button',
            isset($data['class_name']) ? $data['class_name'] : null
        );
        $block->setData($data);
        return $block->toHtml();
    }
}
