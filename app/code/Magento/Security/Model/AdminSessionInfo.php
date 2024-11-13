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
 * @method int getUserId() getUserId()
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
     * @var ConfigInterface
     * @since 100.1.0
     */
    protected $securityConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

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
