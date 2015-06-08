<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Collection;

/**
 * Concrete implementation of abstract collection, created for abstract collection testing purposes.
 */
class DbCollection extends \Magento\Framework\Data\Collection\AbstractDb
{
    /**
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    private $resource;

    /**
     * Set DB resource for testing purposes.
     *
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     * @return $this
     */
    public function setResource(\Magento\Framework\Model\Resource\Db\AbstractDb $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Get resource instance.
     *
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    public function getResource()
    {
        return $this->resource;
    }
}
