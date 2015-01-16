<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Resource\Entity;

/**
 * Class describing db table resource entity
 *
 */
class Table extends \Magento\Framework\Model\Resource\Entity\AbstractEntity
{
    /**
     * Get table
     *
     * @return String
     */
    public function getTable()
    {
        return $this->getConfig('table');
    }
}
