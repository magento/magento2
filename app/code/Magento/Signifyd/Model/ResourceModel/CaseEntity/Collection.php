<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\ResourceModel\CaseEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Signifyd\Model\CaseEntity;
use Magento\Signifyd\Model\ResourceModel\CaseEntity as CaseResourceModel;

/**
 * Collection of case entities
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init(CaseEntity::class, CaseResourceModel::class);
    }
}
