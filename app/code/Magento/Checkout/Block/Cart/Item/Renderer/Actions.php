<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Text;
use Magento\Quote\Model\Quote\Item;

class Actions extends Text
{
    /**
     * @var Item
     */
    protected $item;

    /**
     * Returns current quote item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set current quote item
     *
     * @param Item $item
     * @return $this
     */
    public function setItem(Item $item)
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
