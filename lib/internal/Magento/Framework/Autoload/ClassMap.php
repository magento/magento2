<?php
/**
 * An autoloader that uses class map
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload;

class ClassMap
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
    protected $_map = [];

    /**
     * Set base directory absolute path
     *
     * @param string $baseDir
     * @throws \InvalidArgumentException
     */
    public function __construct($baseDir)
    {
        $this->_baseDir = realpath($baseDir);
        if (!$this->_baseDir || !is_dir($this->_baseDir)) {
            throw new \InvalidArgumentException("Specified path is not a valid directory: '{$baseDir}'");
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
            return $this->_baseDir . '/' . $this->_map[$class];
        }
        return false;
    }

    /**
     * Add classes files declaration to the map. New map will override existing values if such was defined before.
     *
     * @param array $map
     * @return $this
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
     * @return void
     */
    public function load($class)
    {
        $file = $this->getFile($class);
        if (file_exists($file)) {
            include $file;
        }
    }
}
