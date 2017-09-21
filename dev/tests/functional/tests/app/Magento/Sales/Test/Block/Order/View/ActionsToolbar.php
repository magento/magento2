<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Order\View;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Order view block.
 */
class ActionsToolbar extends Block
{
    /**
     * Link selector.
     *
     * @var string
     */
    protected $linkSelector = './/a[contains(@class, "action")]//span[contains(text(), "%s")]';

    /**
     * Click link on this page.
     *
     * @param string $linkName
     * @throws \Exception
     */
    public function clickLink($linkName)
    {
        $link = $this->_rootElement->find(sprintf($this->linkSelector, $linkName), Locator::SELECTOR_XPATH);
        if (!$link->isVisible()) {
            throw new \Exception(sprintf('"%s" link is not visible', $linkName));
        }
        $link->click();
    }
}
