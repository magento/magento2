<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Interface ConfigInterface
 * @api
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Retrieve renderer for area from config
     *
     * @param string $section
     * @param string $group
     * @param string $code
     * @return array
     * @since 2.0.0
     */
    public function getTotalsRenderer($section, $group, $code);

    /**
     * Retrieve totals for group
     * e.g. quote, etc
     *
     * @param string $section
     * @param string $group
     * @return array
     * @since 2.0.0
     */
    public function getGroupTotals($section, $group);

    /**
     * Get available product types
     *
     * @return array
     * @since 2.0.0
     */
    public function getAvailableProductTypes();
}
