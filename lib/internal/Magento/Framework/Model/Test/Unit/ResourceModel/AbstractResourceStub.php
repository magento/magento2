<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel;

use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;

class AbstractResourceStub extends AbstractResource
{
    /**
     * @var AdapterInterface
     */
    private $connectionAdapter;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        return null;
    }

    /**
     * Get connection
     *
     * @return AdapterInterface
     */
    public function getConnection()
    {
        return $this->connectionAdapter;
    }

    /**
     * @param AdapterInterface $adapter
     *
     * @return void
     */
    public function setConnection(AdapterInterface $adapter)
    {
        $this->connectionAdapter = $adapter;
    }
}
