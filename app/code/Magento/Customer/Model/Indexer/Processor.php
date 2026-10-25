<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Model\Indexer;

use Magento\Customer\Model\Customer;

/**
 * Customer indexer
 */
class Processor extends \Magento\Framework\Indexer\AbstractProcessor
{
    const INDEXER_ID = Customer::CUSTOMER_GRID_INDEXER_ID;
}
