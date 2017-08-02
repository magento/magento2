<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

/**
 * Class PhpInformation
 *
 * Provides information and checks about the current and required PHP settings.
 * @since 2.0.0
 */
class PhpInformation
{

    /**
     * Allowed XDebug nested level
     */
    const XDEBUG_NESTED_LEVEL = 200;

    /**
     * List of currently installed extensions
     *
     * @var array
     * @since 2.0.0
     */
    protected $current = [];

    /**
     * Returns minimum required XDebug nested level
     * @return int
     * @since 2.0.0
     */
    public function getRequiredMinimumXDebugNestedLevel()
    {
        return self::XDEBUG_NESTED_LEVEL;
    }

    /**
     * Retrieve list of currently installed extensions
     *
     * @return array
     * @since 2.0.0
     */
    public function getCurrent()
    {
        if (!$this->current) {
            $this->current = array_map(function ($ext) {
                return str_replace(' ', '-', strtolower($ext));
            }, get_loaded_extensions());
        }
        return $this->current;
    }
}
