<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Import currency model interface
 */
namespace Magento\Directory\Model\Currency\Import;

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
