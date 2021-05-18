<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Security\Model\UserExpiration as UserExpirationModel;

/**
 * Admin User Expiration resource model
 */
class UserExpiration extends AbstractDb
{

    /**
     * Flag that notifies whether Primary key of table is auto-incremented
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param Context $context
     * @param TimezoneInterface $timezone
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        TimezoneInterface $timezone,
        ?string $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->timezone = $timezone;
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
     * @param AbstractModel $userExpiration
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $userExpiration)
    {
        /** @var $userExpiration UserExpirationModel */
        $utcValue = $this->timezone->convertConfigTimeToUtc($this->timezone->date($userExpiration->getExpiresAt()));
        $userExpiration->setExpiresAt($utcValue);

        return $this;
    }

    /**
     * Convert to store time.
     *
     * @param AbstractModel $userExpiration
     * @return $this|AbstractDb
     * @throws \Exception
     */
    protected function _afterLoad(AbstractModel $userExpiration)
    {
        /** @var $userExpiration UserExpirationModel */
        if ($userExpiration->getExpiresAt()) {
            $storeValue = $this->timezone->date(new \DateTime($userExpiration->getExpiresAt()));
            $userExpiration->setExpiresAt($storeValue->format('Y-m-d H:i:s'));
        }

        return $this;
    }
}
