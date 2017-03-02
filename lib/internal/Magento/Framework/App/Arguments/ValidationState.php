<?php
/**
 * Application config file resolver
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Arguments;

class ValidationState implements \Magento\Framework\Config\ValidationStateInterface
{
    /**
     * @var string
     */
    protected $_appMode;

    /**
     * @param string $appMode
     */
    public function __construct($appMode)
    {
        $this->_appMode = $appMode;
    }

    /**
     * Retrieve current validation state
     *
     * @return boolean
     */
    public function isValidationRequired()
    {
        return $this->_appMode == \Magento\Framework\App\State::MODE_DEVELOPER;
    }
}
