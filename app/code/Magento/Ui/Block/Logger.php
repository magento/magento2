<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block;

use Magento\Framework\View\Element\Template;

class Logger extends Template
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
     * Is session storage logging enabled
     *
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LOGGING);
    }

    /**
     * Get session storage key
     *
     * @return string
     */
    public function getSessionStorageKey()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_KEY);
    }
}
