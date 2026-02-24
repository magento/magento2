<?php
/**
 * Configuration interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\App\Config;

/**
 * @api
 * @since 100.0.2
 */
interface MutableScopeConfigInterface extends \Magento\Framework\App\Config\ScopeConfigInterface
{
    /**
     * Set config value in the corresponding config scope
     *
     * @param string $path
     * @param mixed $value
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return void
     */
    public function setValue(
        $path,
        $value,
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    );
}
