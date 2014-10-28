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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * The class for keeping scenario configuration
 */
namespace Magento\TestFramework\Performance;

class Scenario
{
    /**#@+
     * Common scenario arguments
     */
    const ARG_USERS = 'users';

    const ARG_LOOPS = 'loops';

    const ARG_HOST = 'host';

    const ARG_PATH = 'path';

    const ARG_BASEDIR = 'basedir';

    const ARG_ADMIN_USERNAME = 'admin_username';

    const ARG_ADMIN_PASSWORD = 'admin_password';

    const ARG_BACKEND_FRONTNAME = 'backend_frontname';

    /**#@-*/

    /**
     * Scenario title
     *
     * @var string
     */
    protected $_title;

    /**
     * File path
     *
     * @var string
     */
    protected $_file;

    /**
     * Framework settings
     *
     * @var array
     */
    protected $_settings;

    /**
     * Arguments, passed to scenario
     *
     * @var array
     */
    protected $_arguments;

    /**
     * Fixtures, needed to be applied before scenario execution
     *
     * @var array
     */
    protected $_fixtures;

    /**
     * Constructor
     *
     * @param string $title
     * @param string $file
     * @param array $arguments
     * @param array $settings
     * @param array $fixtures
     * @throws \InvalidArgumentException
     */
    public function __construct($title, $file, array $arguments, array $settings, array $fixtures)
    {
        if (!strlen($title)) {
            throw new \InvalidArgumentException('Title must be defined for a scenario');
        }

        $arguments += array(self::ARG_USERS => 1, self::ARG_LOOPS => 1);
        foreach (array(self::ARG_USERS, self::ARG_LOOPS) as $argName) {
            if (!is_int($arguments[$argName]) || $arguments[$argName] < 1) {
                throw new \InvalidArgumentException(
                    "Scenario '{$title}' must have a positive integer argument '{$argName}'."
                );
            }
        }

        $this->_title = $title;
        $this->_file = $file;
        $this->_arguments = $arguments;
        $this->_settings = $settings;
        $this->_fixtures = $fixtures;
    }

    /**
     * Retrieve title of the scenario
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Retrieve file of the scenario
     *
     * @return string
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * Retrieve arguments of the scenario
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Retrieve framework settings of the scenario
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    /**
     * Retrieve fixtures of the scenario
     *
     * @return array
     */
    public function getFixtures()
    {
        return $this->_fixtures;
    }
}
