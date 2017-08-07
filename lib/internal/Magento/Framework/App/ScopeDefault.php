<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Class ScopeDefault
 * @since 2.2.0
 */
class ScopeDefault implements ScopeInterface
{
    /**
     * Retrieve scope code
     *
     * @return string
     * @since 2.2.0
     */
    public function getCode()
    {
        return 'default';
    }

    /**
     * Get scope identifier
     *
     * @return int
     * @since 2.2.0
     */
    public function getId()
    {
        return 1;
    }

    /**
     * Get scope type
     *
     * @return string
     * @since 2.2.0
     */
    public function getScopeType()
    {
        return self::SCOPE_DEFAULT;
    }

    /**
     * Get scope type name
     *
     * @return string
     * @since 2.2.0
     */
    public function getScopeTypeName()
    {
        return 'Default Scope';
    }

    /**
     * Get scope name
     *
     * @return string
     * @since 2.2.0
     */
    public function getName()
    {
        return 'Default';
    }
}
