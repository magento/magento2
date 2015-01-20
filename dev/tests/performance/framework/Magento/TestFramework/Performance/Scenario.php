<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

        $arguments += [self::ARG_USERS => 1, self::ARG_LOOPS => 1];
        foreach ([self::ARG_USERS, self::ARG_LOOPS] as $argName) {
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
