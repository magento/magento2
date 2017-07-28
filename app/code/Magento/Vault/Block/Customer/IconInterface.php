<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block\Customer;

/**
 * Interface IconInterface
 * @since 2.2.0
 */
interface IconInterface
{
    /**
     * Get url to icon
     * @return string
     * @since 2.2.0
     */
    public function getIconUrl();

    /**
     * Get width of icon
     * @return int
     * @since 2.2.0
     */
    public function getIconHeight();

    /**
     * Get height of icon
     * @return int
     * @since 2.2.0
     */
    public function getIconWidth();
}
