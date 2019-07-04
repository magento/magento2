<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\ResourceModel;

/**
 * Admin User Expiration resource model
 */
class UserExpiration extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Flag that notifies whether Primary key of table is auto-incremented
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('admin_user_expiration', 'user_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var $object \Magento\Security\Model\UserExpiration */
        if ($object->getExpiresAt() instanceof \DateTimeInterface) {

            // TODO: use this? need to check if we're ever passing in a \DateTimeInterface or if it's always a string
            $object->setExpiresAt($object->getExpiresAt()->format('Y-m-d H:i:s'));
        }

        return $this;
    }
}
