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
 * @since 100.0.2
 */
interface ImportInterface
{
    /**
     * Import rates
     *
     * @return \Magento\Directory\Model\Currency\Import\AbstractImport
     */
    public function importRates();

    /**
     * Fetch rates
     *
     * @return array
     */
    public function fetchRates();

    /**
     * Return messages
     *
     * @return array
     */
    public function getMessages();
}
