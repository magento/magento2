<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Entity;

/**
 * Class describing db table resource entity
 *
 * @since 2.0.0
 */
class Table extends \Magento\Framework\Model\ResourceModel\Entity\AbstractEntity
{
    /**
     * Get table
     *
     * @return String
     * @since 2.0.0
     */
    public function getTable()
    {
        return $this->getConfig('table');
    }
}
