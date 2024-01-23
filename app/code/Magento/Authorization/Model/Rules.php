<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Model;

/**
 * Admin Rules Model
 *
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
 * @api
 * @since 100.0.2
 */
class Rules extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Authorization\Model\ResourceModel\Rules::class);
    }

    /**
     * Obsolete method of update
     *
     * @return $this
     * @deprecated Method was never implemented and used.
     */
    public function update()
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        trigger_error('Method was never implemented and used.', E_USER_DEPRECATED);

        return $this;
    }

    /**
     * Save authorization rule relation
     *
     * @return $this
     */
    public function saveRel()
    {
        $this->getResource()->saveRel($this);
        return $this;
    }
}
