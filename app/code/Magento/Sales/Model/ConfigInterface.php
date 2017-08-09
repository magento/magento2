<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Interface ConfigInterface
 * @api
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
     */
    public function getTotalsRenderer($section, $group, $code);

    /**
     * Retrieve totals for group
     * e.g. quote, etc
     *
     * @param string $section
     * @param string $group
     * @return array
     */
    public function getGroupTotals($section, $group);

    /**
     * Get available product types
     *
     * @return array
     */
    public function getAvailableProductTypes();
}
