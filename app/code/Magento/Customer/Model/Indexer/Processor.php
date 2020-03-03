<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
