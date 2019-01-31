<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * @api
 */
interface ScopeInterface
{
    /**
     * Default scope reference code
     */
    const SCOPE_DEFAULT = 'default';

    /**
     * Retrieve scope code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get scope identifier
     *
     * @return int
     */
    public function getId();

    /**
     * Get scope type
     *
     * @return string
     * @since 100.1.0
     */
    public function getScopeType();

    /**
     * Get scope type name
     *
     * @return string
     * @since 100.1.0
     */
    public function getScopeTypeName();

    /**
     * Get scope name
     *
     * @return string
     * @since 100.1.0
     */
    public function getName();
}
