<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Implementation of case resource model
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class CaseEntity extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('signifyd_case', 'entity_id');
    }
}
