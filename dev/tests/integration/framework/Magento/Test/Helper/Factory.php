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
 * @category    Magento
 * @package     Magento_Test
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Factory for helpers, used in Magento testing framework
 */
class Magento_Test_Helper_Factory
{
    /**
     * @var array
     */
    static protected $_instances = array();

    /**
     * Retrieves singleton instance of helper
     *
     * @param string $name
     * @return mixed
     */
    static public function getHelper($name)
    {
        if (!isset(self::$_instances[$name])) {
            $className = preg_replace('/[^_]*$/', ucfirst($name), __CLASS__, 1);
            self::$_instances[$name] = new $className();
        }
        return self::$_instances[$name];
    }

    /**
     * Sets custom helper instance to be used for specific name, or null to clear instance.
     * Returns previous instance (if any) or null (if no helper was defined).
     *
     * @param string $name
     * @param mixed $helper
     * @return mixed
     */
    static public function setHelper($name, $helper)
    {
        $old = isset(self::$_instances[$name]) ? self::$_instances[$name] : null;
        self::$_instances[$name] = $helper;
        return $old;
    }
}
