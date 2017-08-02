<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class \Magento\Ui\Model\Config
 *
 * @since 2.0.0
 */
class Config
{
    /**
     * Configuration path to session storage logging setting
     */
    const XML_PATH_LOGGING = 'dev/js/session_storage_logging';

    /**
     * Configuration path to session storage key setting
     */
    const XML_PATH_KEY = 'dev/js/session_storage_key';

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.0.0
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is session storage logging enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isLoggingEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LOGGING);
    }

    /**
     * Get session storage key
     *
     * @return string
     * @since 2.0.0
     */
    public function getSessionStorageKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_KEY);
    }
}
