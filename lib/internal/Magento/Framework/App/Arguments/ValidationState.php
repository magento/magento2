<?php
/**
 * Application config file resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Arguments;

/**
 * Class \Magento\Framework\App\Arguments\ValidationState
 *
 * @since 2.0.0
 */
class ValidationState implements \Magento\Framework\Config\ValidationStateInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_appMode;

    /**
     * @param string $appMode
     * @since 2.0.0
     */
    public function __construct($appMode)
    {
        $this->_appMode = $appMode;
    }

    /**
     * Retrieve current validation state
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isValidationRequired()
    {
        return $this->_appMode == \Magento\Framework\App\State::MODE_DEVELOPER;
    }
}
