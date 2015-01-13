<?php
/**
 * Application config file resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
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
    public function isValidated()
    {
        return $this->_appMode == \Magento\Framework\App\State::MODE_DEVELOPER;
    }
}
