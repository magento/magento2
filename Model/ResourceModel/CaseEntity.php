<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Implementation of case resource model
 * @since 2.2.0
 */
class CaseEntity extends AbstractDb
{
    /**
     * @inheritdoc
     * @since 2.2.0
     */
    protected function _construct()
    {
        $this->_init('signifyd_case', 'entity_id');
    }
}
