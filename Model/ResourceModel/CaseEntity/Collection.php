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
 * @since 2.2.0
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function _construct()
    {
        $this->_init(CaseEntity::class, CaseResourceModel::class);
    }
}
