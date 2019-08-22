<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Block\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Block of tabs on Stores > Settings > Configuration page.
 */
class Tabs extends Block
{
    /**
     * Top tab selector.
     *
     * @var string
     */
    private $topTab = ".//div[@role='tab' and *[contains(.,'%s')]]";

    /**
     * Selector of block with sub tabs.
     *
     * @var string
     */
    private $subTabs = "./following-sibling::ul";

    /**
     * Selector of specific sub tab name.
     *
     * @var string
     */
    private $subTabName = 'li a span';

    /**
     * Get list of sub tabs names of tab.
     *
     * @param string $tab
     * @return array
     */
    public function getSubTabsNames($tab)
    {
        $subTabsNames = [];

        $topTab = $this->_rootElement->find(sprintf($this->topTab, $tab), Locator::SELECTOR_XPATH);
        $subTabsBlock = $topTab->find($this->subTabs, Locator::SELECTOR_XPATH);

        if (!$subTabsBlock->isVisible()) {
            $topTab->click();
        }

        foreach ($subTabsBlock->getElements($this->subTabName) as $elements) {
            $subTabsNames[] = $elements->getText();
        }

        return $subTabsNames;
    }
}
