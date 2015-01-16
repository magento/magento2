<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Quote;

use Magento\Framework\Model\Resource\Db\AbstractDb;

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
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource
    ) {
        parent::__construct($resource);
    }

    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_quote_payment', 'payment_id');
    }
}
