<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Dependency\Data;

/**
 * Dependency
 * @since 2.0.0
 */
class Dependency
{
    /**#@+
     * Dependencies types
     */
    const TYPE_HARD = 'hard';

    const TYPE_SOFT = 'soft';

    /**#@-*/

    /**
     * Module we depend on
     *
     * @var string
     * @since 2.0.0
     */
    protected $module;

    /**
     * Dependency type
     *
     * @var string
     * @since 2.0.0
     */
    protected $type;

    /**
     * Dependency construct
     *
     * @param string $module
     * @param string $type One of self::TYPE_* constants
     * @since 2.0.0
     */
    public function __construct($module, $type = self::TYPE_HARD)
    {
        $this->module = $module;

        $this->type = self::TYPE_SOFT == $type ? self::TYPE_SOFT : self::TYPE_HARD;
    }

    /**
     * Get module
     *
     * @return string
     * @since 2.0.0
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Get type
     *
     * @return string
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Check is hard dependency
     *
     * @return bool
     * @since 2.0.0
     */
    public function isHard()
    {
        return self::TYPE_HARD == $this->getType();
    }

    /**
     * Check is soft dependency
     *
     * @return bool
     * @since 2.0.0
     */
    public function isSoft()
    {
        return self::TYPE_SOFT == $this->getType();
    }
}
