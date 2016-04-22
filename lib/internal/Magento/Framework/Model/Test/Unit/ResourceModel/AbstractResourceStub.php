<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel;

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
     * @param \Magento\Framework\DataObject $object
     * @param string $field
     * @param null $defaultValue
     * @param bool $unsetEmpty
     * @return $this
     */
    public function _serializeField(\Magento\Framework\DataObject $object, $field, $defaultValue = null, $unsetEmpty = false)
    {
        return parent::_serializeField($object, $field, $defaultValue, $unsetEmpty);
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @param string $field
     * @param null $defaultValue
     */
    public function _unserializeField(\Magento\Framework\DataObject $object, $field, $defaultValue = null)
    {
        parent::_unserializeField($object, $field, $defaultValue);
    }
}
