<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context as ModelContext;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeModel;
use Magento\Security\Model\ResourceModel\AdminSessionInfo as ResourceAdminSessionInfo;

/**
 * Admin Session Info Model
 *
 * @method int getUserId() getUserId()
 * @method int getStatus()
 * @method string getUpdatedAt()
 * @method string getCreatedAt()
 *
 * @api
 * @since 100.1.0
 */
class AdminSessionInfo extends AbstractModel
{
    /**
     * Admin session status definition
     */

    /**
     * Admin logged in
     */
    public const LOGGED_IN = 1;

    /**
     * Admin logged out
     */
    public const LOGGED_OUT = 0;

    /**
     * User has been logged out by another login with the same credentials
     */
    public const LOGGED_OUT_BY_LOGIN = 2;

    /**
     * User has been logged out manually from another session
     */
    public const LOGGED_OUT_MANUALLY = 3;

    /**
     * All other open sessions were terminated
     * @since 100.1.0
     * @var bool
     */
    protected $isOtherSessionsTerminated = false;

    /**
     * AdminSessionInfo constructor
     *
     * @param ModelContext $context
     * @param Registry $registry
     * @param ConfigInterface $securityConfig
     * @param DateTimeModel $dateTime
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ModelContext $context,
        Registry $registry,
        protected readonly ConfigInterface $securityConfig,
        private readonly DateTimeModel $dateTime,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @since 100.1.0
     */
    protected function _construct()
    {
        $this->_init(ResourceAdminSessionInfo::class);
    }

    /**
     * Check if a status is logged in
     *
     * @return bool
     * @since 100.1.0
     */
    public function isLoggedInStatus()
    {
        $this->checkActivity();
        return $this->getData('status') == self::LOGGED_IN;
    }

    /**
     * Check if session is timed out and set status accordingly
     *
     * @return void
     */
    private function checkActivity()
    {
        if ($this->isSessionExpired()) {
            $this->setData('status', self::LOGGED_OUT);
        }
    }

    /**
     * Check whether the session is expired
     *
     * @return bool
     * @since 100.1.0
     */
    public function isSessionExpired()
    {
        $lifetime = $this->securityConfig->getAdminSessionLifetime();
        $currentTime = $this->dateTime->gmtTimestamp();
        $lastUpdatedTime = $this->getUpdatedAt();
        if (!is_numeric($lastUpdatedTime)) {
            $lastUpdatedTime = $lastUpdatedTime === null ? 0 : strtotime($lastUpdatedTime);
        }

        return $lastUpdatedTime <= ($currentTime - $lifetime);
    }

    /**
     * Get formatted IP
     *
     * @return string
     * @since 100.1.0
     */
    public function getFormattedIp()
    {
        return $this->getIp();
    }

    /**
     * Check if other sessions terminated
     *
     * @return bool
     * @since 100.1.0
     */
    public function isOtherSessionsTerminated()
    {
        return $this->isOtherSessionsTerminated;
    }

    /**
     * Setter for isOtherSessionsTerminated
     *
     * @param bool $isOtherSessionsTerminated
     * @return $this
     * @since 100.1.0
     */
    public function setIsOtherSessionsTerminated($isOtherSessionsTerminated)
    {
        $this->isOtherSessionsTerminated = (bool) $isOtherSessionsTerminated;
        return $this;
    }
}
