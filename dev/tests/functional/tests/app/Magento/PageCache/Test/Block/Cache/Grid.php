<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Block\Cache;

use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Widget\Grid as ParentGrid;

/**
 * Backend Cache Management grid.
 */
class Grid extends ParentGrid
{
    /**
     * Locator value for cache status.
     *
     * @var string
     */
    private $cacheStatus = "//tr[td[contains(text(), '%s')]]/td//span[contains(text(), '%s')]";

    /**
     * Search for item and select it.
     *
     * @param array $filter
     * @return void
     * @throws \Exception
     */
    public function searchAndSelect(array $filter)
    {
        $selectItem = $this->getRow($filter, false)->find($this->selectItem);
        if ($selectItem->isVisible()) {
            $selectItem->click();
        } else {
            throw new \Exception("Searched item was not found by filter\n" . print_r($filter, true));
        }
    }

    /**
     * Checks cache status.
     *
     * @param string $cacheType
     * @param string $cacheStatus
     * @return bool
     */
    public function isCacheStatusCorrect($cacheType, $cacheStatus)
    {
        return $this->_rootElement->find(sprintf($this->cacheStatus, $cacheType, $cacheStatus), Locator::SELECTOR_XPATH)
            ->isVisible();
    }
}
