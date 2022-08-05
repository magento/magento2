<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Collection;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Concrete implementation of abstract collection, created for abstract collection testing purposes.
 */
class DbCollection extends AbstractDb
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    private $resource;

    /**
     * Set DB resource for testing purposes.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @return $this
     */
    public function setResource(\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Get resource instance.
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getResource()
    {
        return $this->resource;
    }
}
