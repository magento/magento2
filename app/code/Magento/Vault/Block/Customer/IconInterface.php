<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Vault\Block\Customer;

/**
 * Interface IconInterface
 *
 * @api
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
