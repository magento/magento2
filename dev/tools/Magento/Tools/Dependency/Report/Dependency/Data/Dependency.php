<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Dependency\Report\Dependency\Data;

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

    /**
     * Module we depend on
     *
     * @var string
     */
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
