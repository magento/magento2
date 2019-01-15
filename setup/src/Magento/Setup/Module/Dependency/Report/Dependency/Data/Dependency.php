<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Dependency\Data;

/**
 * Dependency
 */
class Dependency
{
    /**#@+
     * Dependencies types
     */
    const TYPE_HARD = 'hard';

    const TYPE_SOFT = 'soft';

    /**#@-*/

    /**#@-*/
    protected $module;

    /**
     * Dependency type
     *
     * @var string
     */
    protected $type;

    /**
     * Dependency construct
     *
     * @param string $module
     * @param string $type One of self::TYPE_* constants
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
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Check is hard dependency
     *
     * @return bool
     */
    public function isHard()
    {
        return self::TYPE_HARD == $this->getType();
    }

    /**
     * Check is soft dependency
     *
     * @return bool
     */
    public function isSoft()
    {
        return self::TYPE_SOFT == $this->getType();
    }
}
