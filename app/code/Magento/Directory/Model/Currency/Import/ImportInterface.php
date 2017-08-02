<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model\Currency\Import;

/**
 * Import currency model interface
 *
 * @api
 * @since 2.0.0
 */
interface ImportInterface
{
    /**
     * Import rates
     *
     * @return \Magento\Directory\Model\Currency\Import\AbstractImport
     * @since 2.0.0
     */
    public function importRates();

    /**
     * Fetch rates
     *
     * @return array
     * @since 2.0.0
     */
    public function fetchRates();

    /**
     * Return messages
     *
     * @return array
     * @since 2.0.0
     */
    public function getMessages();
}
