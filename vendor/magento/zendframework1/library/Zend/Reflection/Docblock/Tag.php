<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Loader */
#require_once 'Zend/Loader.php';

/**
 * @category   Zend
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Reflection_Docblock_Tag implements Reflector
{
    /**
     * @var array Array of Class names
     */
    protected static $_tagClasses = array(
        'param'  => 'Zend_Reflection_Docblock_Tag_Param',
        'return' => 'Zend_Reflection_Docblock_Tag_Return',
        );

    /**
     * @var string
     */
    protected $_name = null;

    /**
     * @var string
     */
    protected $_description = null;

    /**
     * Factory: Create the appropriate annotation tag object
     *
     * @param  string $tagDocblockLine
     * @return Zend_Reflection_Docblock_Tag
     */
    public static function factory($tagDocblockLine)
    {
        $matches = array();

        if (!preg_match('#^@(\w+)(\s|$)#', $tagDocblockLine, $matches)) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('No valid tag name found within provided docblock line.');
        }

        $tagName = $matches[1];
        if (array_key_exists($tagName, self::$_tagClasses)) {
            $tagClass = self::$_tagClasses[$tagName];
            if (!class_exists($tagClass)) {
                Zend_Loader::loadClass($tagClass);
            }
            return new $tagClass($tagDocblockLine);
        }
        return new self($tagDocblockLine);
    }

    /**
     * Export reflection
     *
     * Required by Reflector
     *
     * @todo   What should this do?
     * @return void
     */
    public static function export()
    {
    }

    /**
     * Serialize to string
     *
     * Required by Reflector
     *
     * @todo   What should this do?
     * @return string
     */
    public function __toString()
    {
        $str = "Docblock Tag [ * @".$this->_name." ]".PHP_EOL;

        return $str;
    }

    /**
     * Constructor
     *
     * @param  string $tagDocblockLine
     * @return void
     */
    public function __construct($tagDocblockLine)
    {
        $matches = array();

        // find the line
        if (!preg_match('#^@(\w+)(?:\s+([^\s].*)|$)?#', $tagDocblockLine, $matches)) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Provided docblock line does not contain a valid tag');
        }

        $this->_name = $matches[1];
        if (isset($matches[2]) && $matches[2]) {
            $this->_description = $matches[2];
        }
    }

    /**
     * Get annotation tag name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get annotation tag description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }
}
