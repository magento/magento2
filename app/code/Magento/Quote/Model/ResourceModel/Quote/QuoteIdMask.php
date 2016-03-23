<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * QuoteIdMask Resource model
 * @codeCoverageIgnore
 */
class QuoteIdMask extends AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_id_mask', 'entity_id');
    }
}
