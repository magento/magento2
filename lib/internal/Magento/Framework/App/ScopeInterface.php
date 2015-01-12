<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

interface ScopeInterface
{
    /**
     * Default scope type
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
     * @return  int
     */
    public function getId();
}
