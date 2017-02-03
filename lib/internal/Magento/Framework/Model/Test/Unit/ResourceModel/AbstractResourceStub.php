<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel;

use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\AbstractResource;

class AbstractResourceStub extends AbstractResource
{
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
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection()
    {
        return null;
    }

    /**
     * @param DataObject $object
     * @param string $field
     * @param null $defaultValue
     * @param bool $unsetEmpty
     * @return $this
     */
    public function _serializeField(DataObject $object, $field, $defaultValue = null, $unsetEmpty = false)
    {
        return parent::_serializeField($object, $field, $defaultValue, $unsetEmpty);
    }

    /**
     * @param DataObject $object
     * @param string $field
     * @param null $defaultValue
     */
    public function _unserializeField(DataObject $object, $field, $defaultValue = null)
    {
        parent::_unserializeField($object, $field, $defaultValue);
    }
}
