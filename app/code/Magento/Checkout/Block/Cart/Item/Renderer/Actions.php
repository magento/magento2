<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Text;

class Actions extends Text
{
    /**
     * @var Context
     */
    protected $itemContext;

    /**
     * Returns current quote item
     *
     * @return Context
     */
    public function getItemContext()
    {
        return $this->itemContext;
    }

    /**
     * Set current quote item
     *
     * @param Context $itemContext
     */
    public function setItemContext(Context $itemContext)
    {
        $this->itemContext = $itemContext;
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
                $childBlock->setItemContext($this->getItemContext());
                $this->addText($layout->renderElement($child, false));
            }
        }

        return parent::_toHtml();
    }
}
