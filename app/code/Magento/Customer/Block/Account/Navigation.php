<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Account;

use \Magento\Framework\View\Element\Html\Links;
use \Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Class for sorting links in navigation panels.
 *
 * @api
 * @since 100.2.0
 */
class Navigation extends Links
{
    /**
     * {@inheritdoc}
     * @since 100.2.0
     */
    public function getLinks()
    {
        $links = $this->_layout->getChildBlocks($this->getNameInLayout());
        $sortableLink = [];
        foreach ($links as $key => $link) {
            if ($link instanceof SortLinkInterface) {
                $sortableLink[] = $link;
                unset($links[$key]);
            }
        }

        usort($sortableLink, [$this, "compare"]);
        return array_merge($sortableLink, $links);
    }

    /**
     * Compare sortOrder in links.
     *
     * @param SortLinkInterface $firstLink
     * @param SortLinkInterface $secondLink
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function compare(SortLinkInterface $firstLink, SortLinkInterface $secondLink)
    {
        return ($firstLink->getSortOrder() < $secondLink->getSortOrder());
    }
}
