<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block\Customer;

/**
 * Interface IconInterface
 */
interface IconInterface
{
    /**
     * Get url to icon
     * @return string
     */
    public function getIconUrl();

    /**
     * Get width of icon
     * @return int
     */
    public function getIconHeight();

    /**
     * Get height of icon
     * @return int
     */
    public function getIconWidth();
}
