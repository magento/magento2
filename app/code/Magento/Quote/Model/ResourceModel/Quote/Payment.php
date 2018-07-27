<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Quote payment resource model
 */
class Payment extends AbstractDb
{
    /**
     * Serializeable field: additional_information
     *
     * @var array
     */
    protected $_serializableFields = ['additional_information' => [null, []]];

    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_payment', 'payment_id');
    }
}
