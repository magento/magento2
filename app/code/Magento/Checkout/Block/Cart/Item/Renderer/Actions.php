<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Text;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @api
 * @since 100.0.2
 */
class Actions extends Text
{
    /**
     * @var AbstractItem
     */
    protected $item;

    /**
     * Returns current quote item
     *
     * @return AbstractItem
     * @codeCoverageIgnore
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set current quote item
     *
     * @param AbstractItem $item
     * @return $this
     * @codeCoverageIgnore
     */
    public function setItem(AbstractItem $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setText('');

        $layout = $this->getLayout();
        foreach ($this->getChildNames() as $child) {
            /** @var Generic $childBlock */
            $childBlock = $layout->getBlock($child);
            if ($childBlock instanceof Generic) {
                $childBlock->setItem($this->getItem());
                $this->addText($layout->renderElement($child, false));
            }
        }

        return parent::_toHtml();
    }
}
