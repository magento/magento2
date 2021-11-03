<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model;

/**
 * Admin Session Info Model
 *
 * @method int getUserId()
 * @method int getStatus()
 * @method string getUpdatedAt()
 * @method string getCreatedAt()
 *
 * @api
 * @since 100.1.0
 */
class AdminSessionInfo extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Admin session status definition
     */

    /**
     * Admin logged in
     */
    const LOGGED_IN = 1;

    /**
     * Admin logged out
     */
    const LOGGED_OUT = 0;

    /**
     * User has been logged out by another login with the same credentials
     */
    const LOGGED_OUT_BY_LOGIN = 2;

    /**
     * User has been logged out manually from another session
     */
    const LOGGED_OUT_MANUALLY = 3;

    /**
     * All other open sessions were terminated
     * @since 100.1.0
     */
    protected bool $isOtherSessionsTerminated = false;

    /**
     * @var ConfigInterface
     * @since 100.1.0
     */
    protected ConfigInterface $securityConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private \Magento\Framework\Stdlib\DateTime\DateTime $dateTime;

    /**
     * AdminSessionInfo constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ConfigInterface $securityConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ConfigInterface $securityConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->securityConfig = $securityConfig;
        $this->dateTime = $dateTime;
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @since 100.1.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Security\Model\ResourceModel\AdminSessionInfo::class);
    }

    /**
     * Check if a status is logged in
     *
     * @return bool
     * @since 100.1.0
     */
    public function isLoggedInStatus(): bool
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
    public function isSessionExpired(): bool
    {
        $lifetime = $this->securityConfig->getAdminSessionLifetime();
        $currentTime = $this->dateTime->gmtTimestamp();
        $lastUpdatedTime = $this->getUpdatedAt() ?? $currentTime;
        if (!is_numeric($lastUpdatedTime)) {
            $lastUpdatedTime = strtotime($lastUpdatedTime);
        }

        return $lastUpdatedTime <= ($currentTime - $lifetime);
    }

    /**
     * Get formatted IP
     *
     * @return string
     * @since 100.1.0
     */
    public function getFormattedIp(): string
    {
        return $this->getIp();
    }

    /**
     * Check if other sessions terminated
     *
     * @return bool
     * @since 100.1.0
     */
    public function isOtherSessionsTerminated(): bool
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
    public function setIsOtherSessionsTerminated($isOtherSessionsTerminated): AdminSessionInfo
    {
        $this->isOtherSessionsTerminated = (bool) $isOtherSessionsTerminated;
        return $this;
    }
}
