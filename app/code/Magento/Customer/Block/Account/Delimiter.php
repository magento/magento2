<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Block\Account;

/**
 * Class for delimiter.
 *
 * @api
 * @since 101.0.0
 */
class Delimiter extends \Magento\Framework\View\Element\Template implements SortLinkInterface
{
    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
