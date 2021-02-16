<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Component\Control;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\UiComponent\Control\ControlInterface;

/**
 * Class Container
 *
 * @api
 */
class Container extends AbstractBlock
{
    /**
     * Default button class
     */
    const DEFAULT_CONTROL = Button::class;
    const SPLIT_BUTTON = SplitButton::class;

    /**
     * Create button renderer
     *
     * @param string $blockName
     * @param string $blockClassName
     * @return ControlInterface
     */
    private function createButton($blockName, $blockClassName = null)
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
