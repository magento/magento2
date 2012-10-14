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
 * @package     performance_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class encapsulates read-only performance scenario arguments
 */
class Magento_Performance_Scenario_Arguments extends ArrayObject
{
    /**#@+
     * Common scenario arguments
     */
    const ARG_USERS           = 'users';
    const ARG_LOOPS           = 'loops';
    const ARG_HOST            = 'host';
    const ARG_PATH            = 'path';
    const ARG_ADMIN_USERNAME  = 'admin_username';
    const ARG_ADMIN_PASSWORD  = 'admin_password';
    const ARG_ADMIN_FRONTNAME = 'admin_frontname';
    /**#@-*/

    /**
     * Constructor
     *
     * @param array $arguments
     * @throws UnexpectedValueException
     */
    public function __construct(array $arguments)
    {
        $arguments += array(self::ARG_USERS => 1, self::ARG_LOOPS => 1);
        foreach (array(self::ARG_USERS, self::ARG_LOOPS) as $argName) {
            if (!is_int($arguments[$argName]) || $arguments[$argName] < 1) {
                throw new UnexpectedValueException("Scenario argument '$argName' must be a positive integer.");
            }
        }
        parent::__construct($arguments);
    }

    /**
     * Retrieve number of concurrent threads
     *
     * @return integer
     */
    public function getUsers()
    {
        return $this[self::ARG_USERS];
    }

    /**
     * Retrieve number of scenario execution loops
     *
     * @return integer
     */
    public function getLoops()
    {
        return $this[self::ARG_LOOPS];
    }

    /**
     * Deny assignment operator
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetSet($offset, $value)
    {
        $this->_denyModification();
    }

    /**
     * Deny invocation of unset() function
     *
     * @param mixed $offset
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetUnset($offset)
    {
        $this->_denyModification();
    }

    /**
     * Deny modification operation by throwing an exception
     *
     * @throws LogicException
     */
    protected function _denyModification()
    {
        throw new LogicException('Scenario arguments are read-only.');
    }
}
