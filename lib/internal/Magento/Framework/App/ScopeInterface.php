<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

interface ScopeInterface
{
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
     */
    public function getScopeType();

    /**
     * Get scope type name
     *
     * @return string
     */
    public function getScopeTypeName();

    /**
     * Get scope name
     *
     * @return string
     */
    public function getName();
}
