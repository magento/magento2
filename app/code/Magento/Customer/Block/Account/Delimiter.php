<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Account;

/**
 * Class for delimiter.
 */
class Delimiter extends \Magento\Framework\View\Element\Template implements SortLinkInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
