<?php
/**
 * Session max size configuration object
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Session;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

/**
 * Magento session max size configuration
 */
class SessionMaxSizeConfig
{
    /**
     * Configuration path to max session size for admin
     */
    const XML_PATH_MAX_SESSION_SIZE_ADMIN = 'system/security/max_session_size_admin';

    /**
     * Configuration path to max session size for storefront
     */
    const XML_PATH_MAX_SESSION_SIZE_STOREFRONT = 'system/security/max_session_size_storefront';

    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var string
     */
    private $_scopeType;

    /**
     * @var State
     */
    private $state;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $scopeType
     * @param State $state
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string $scopeType,
        State $state
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_scopeType = $scopeType;
        $this->state = $state;
    }

    /**
     * Get configuration for session max size
     *
     * @return int|null
     * @throws LocalizedException
     */
    public function getSessionMaxSize(): ?int
    {
        $path = self::XML_PATH_MAX_SESSION_SIZE_STOREFRONT;

        if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
            $path = self::XML_PATH_MAX_SESSION_SIZE_ADMIN;
        }

        $result = (int)$this->_scopeConfig->getValue($path, $this->_scopeType);

        if ($result <= 0) {
            return null;
        } else {
            return $result;
        }
    }
}
