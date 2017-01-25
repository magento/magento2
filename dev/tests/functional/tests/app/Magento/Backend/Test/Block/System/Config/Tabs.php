<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Block\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Tabs.
 */
class Tabs extends Block
{
    /**
     * Top tab selector.
     *
     * @var string
     */
    private $topTab = ".//*//strong[contains(., '%s')]/parent::div";

    /**
     * Selector of block with sub tabs.
     *
     * @var string
     */
    private $subTabs = ".//*//strong[contains(., '%s')]/parent::div/parent::div/ul";

    /**
     * Selector of specific sub tab.
     *
     * @var string
     */
    private $subTab = 'li:nth-of-type(%s)';

    /**
     * Get list of sub tabs of tab.
     *
     * @param string $tab
     * @return array
     */
    public function getSubTabs($tab)
    {
        $subTabs = [];
        $textSelector = 'a span';

        $subTabsBlock = $this->_rootElement->find(sprintf($this->subTabs, $tab), Locator::SELECTOR_XPATH);

        if (!$subTabsBlock->isVisible()) {
            $this->_rootElement->find(sprintf($this->topTab, $tab), Locator::SELECTOR_XPATH)->click();
        }

        $count = 1;
        while ($subTabsBlock->find(sprintf($this->subTab, $count))->isVisible()) {
            $subTabs[] = $subTabsBlock->find(sprintf($this->subTab, $count))
                ->find($textSelector)
                ->getText();
            $count++;
        }

        return $subTabs;
    }
}
