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
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * UserExpiration constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
    }

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
     * Convert to UTC time.
     *
     * @param \Magento\Framework\Model\AbstractModel $userExpiration
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $userExpiration)
    {
        /** @var $userExpiration \Magento\Security\Model\UserExpiration */
        $expiresAt = $userExpiration->getExpiresAt();
        $utcValue = $this->dateTime->gmtDate(null, $expiresAt);
        $userExpiration->setExpiresAt($utcValue);

        return $this;
    }

    /**
     * Convert to store time.
     *
     * @param \Magento\Framework\Model\AbstractModel $userExpiration
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Exception
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $userExpiration)
    {
        /** @var $userExpiration \Magento\Security\Model\UserExpiration */
        if ($userExpiration->getExpiresAt()) {
            $storeValue = $this->timezone->date($userExpiration->getExpiresAt());
            $userExpiration->setExpiresAt($storeValue->format('Y-m-d H:i:s'));
        }

        return $this;
    }
}
