<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db\VersionControl;

/**
 * Interface RelationInterface
 * @since 2.0.0
 */
interface RelationInterface
{
    /**
     * Process object relations
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @since 2.0.0
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object);
}
