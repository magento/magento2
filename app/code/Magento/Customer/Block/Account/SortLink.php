<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Account;

/**
 * Class for sortable links.
 */
class SortLink extends \Magento\Framework\View\Element\Html\Link\Current implements SortLinkInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
