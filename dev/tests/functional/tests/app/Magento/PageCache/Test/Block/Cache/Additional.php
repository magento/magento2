<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Block\Cache;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Additional Cache Management block.
 */
class Additional extends Block
{
    /**
     * Flush button selector.
     *
     * @var string
     */
    protected $flushButton = './/button[normalize-space(.)= "%s"]';

    /**
     * Flush cache in 'Additional Cache Management'.
     *
     * @param string $flushButtonName
     * @return void
     */
    public function clickFlushCache($flushButtonName)
    {
        $this->_rootElement->find(sprintf($this->flushButton, $flushButtonName), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Check if button is visible in 'Additional Cache Management'.
     *
     * @param string $flushButtonName
     * @return bool
     */
    public function isFlushCacheButtonVisible($flushButtonName)
    {
        return $this->_rootElement->find(sprintf($this->flushButton, $flushButtonName), Locator::SELECTOR_XPATH)
                ->isVisible();
    }
}
