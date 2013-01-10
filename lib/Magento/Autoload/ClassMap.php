<?php
/**
 * An autoloader that uses class map
 *
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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Autoload_ClassMap
{
    /**
     * Absolute path to base directory that will be prepended as prefix to the included files
     *
     * @var string
     */
    protected $_baseDir;

    /**
     * Map of class name to file (relative to the base directory)
     *
     * array(
     *     'Class_Name' => 'relative/path/to/Class/Name.php',
     * )
     *
     * @var array
     */
    protected $_map = array();

    /**
     * Set base directory absolute path
     *
     * @param string $baseDir
     * @throws InvalidArgumentException
     */
    public function __construct($baseDir)
    {
        $this->_baseDir = realpath($baseDir);
        if (!$this->_baseDir || !is_dir($this->_baseDir)) {
            throw new InvalidArgumentException("Specified path is not a valid directory: '{$baseDir}'");
        }
    }

    /**
     * Find an absolute path to a file to be included
     *
     * @param string $class
     * @return string|bool
     */
    public function getFile($class)
    {
        if (isset($this->_map[$class])) {
            return $this->_baseDir . DIRECTORY_SEPARATOR . $this->_map[$class];
        }
        return false;
    }

    /**
     * Add classes files declaration to the map. New map will override existing values if such was defined before.
     *
     * @param array $map
     * @return Magento_Autoload_ClassMap
     */
    public function addMap(array $map)
    {
        $this->_map = array_merge($this->_map, $map);
        return $this;
    }

    /**
     * Resolve a class file and include it
     *
     * @param string $class
     */
    public function load($class)
    {
        $file = $this->getFile($class);
        if (file_exists($file)) {
            include $file;
        }
    }
}
