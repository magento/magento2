<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Model;

/**
 * Admin Rules Model
 *
 * @method \Magento\Authorization\Model\ResourceModel\Rules _getResource()
 * @method \Magento\Authorization\Model\ResourceModel\Rules getResource()
 * @method int getRoleId()
 * @method \Magento\Authorization\Model\Rules setRoleId(int $value)
 * @method string getResourceId()
 * @method \Magento\Authorization\Model\Rules setResourceId(string $value)
 * @method string getPrivileges()
 * @method \Magento\Authorization\Model\Rules setPrivileges(string $value)
 * @method int getAssertId()
 * @method \Magento\Authorization\Model\Rules setAssertId(int $value)
 * @method string getPermission()
 * @method \Magento\Authorization\Model\Rules setPermission(string $value)
 */
class Rules extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Authorization\Model\ResourceModel\Rules $resource
     * @param \Magento\Authorization\Model\ResourceModel\Permissions\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Authorization\Model\ResourceModel\Rules $resource,
        \Magento\Authorization\Model\ResourceModel\Permissions\Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Authorization\Model\ResourceModel\Rules');
    }

    /**
     * @return $this
     */
    public function update()
    {
        $this->getResource()->update($this);
        return $this;
    }

    /**
     * @return $this
     */
    public function saveRel()
    {
        $this->getResource()->saveRel($this);
        return $this;
    }
}
