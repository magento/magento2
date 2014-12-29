<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Block\Order\View;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

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
