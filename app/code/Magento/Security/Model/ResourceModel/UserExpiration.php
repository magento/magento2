<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\ResourceModel;

use DateTime;
use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\Timezone\LocalizedDateToUtcConverterInterface;
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
     * @param Context $context
     * @param TimezoneInterface $timezone
     * @param string|null $connectionName
     * @param LocalizedDateToUtcConverterInterface|null $localizedDateToUtcConverter
     */
    public function __construct(
        Context $context,
        private TimezoneInterface $timezone,
        ?string $connectionName = null,
        private ?LocalizedDateToUtcConverterInterface $localizedDateToUtcConverter = null
    ) {
        parent::__construct($context, $connectionName);
        $this->localizedDateToUtcConverter = $localizedDateToUtcConverter ?: ObjectManager::getInstance()
            ->get(LocalizedDateToUtcConverterInterface::class);
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
        /** @var UserExpirationModel $userExpiration */
        $expiresAt = $userExpiration->getExpiresAt();
        $utcValue = strtotime($expiresAt)
            ? $this->timezone->convertConfigTimeToUtc($expiresAt)
            : $this->localizedDateToUtcConverter->convertLocalizedDateToUtc($expiresAt);
        $userExpiration->setExpiresAt($utcValue);

        return $this;
    }

    /**
     * Convert to store time.
     *
     * @param AbstractModel $userExpiration
     * @return $this|AbstractDb
     * @throws Exception
     */
    protected function _afterLoad(AbstractModel $userExpiration)
    {
        /** @var UserExpirationModel $userExpiration */
        if ($userExpiration->getExpiresAt()) {
            $date = new DateTime($userExpiration->getExpiresAt());
            $storeValue = $this->timezone->date($date);
            $userExpiration->setExpiresAt($storeValue->format('Y-m-d H:i:s'));
        }

        return $this;
    }
}
