<?php
/**
 * Application config file resolver
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
