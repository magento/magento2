<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * QuoteIdMask Resource model
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class QuoteIdMask extends AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('quote_id_mask', 'entity_id');
    }
}
