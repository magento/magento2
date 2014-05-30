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
 * @package    Zend_CodeGenerator
 * @subpackage PHP
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Tag.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_CodeGenerator_Abstract
 */
#require_once 'Zend/CodeGenerator/Php/Abstract.php';

/**
 * @see Zend_CodeGenerator_Php_Docblock_Tag_Param
 */
#require_once 'Zend/CodeGenerator/Php/Docblock/Tag/Param.php';

/**
 * @see Zend_CodeGenerator_Php_Docblock_Tag_Return
 */
#require_once 'Zend/CodeGenerator/Php/Docblock/Tag/Return.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_Docblock_Tag extends Zend_CodeGenerator_Php_Abstract
{

    /**
     * @var Zend_Loader_PluginLoader
     */
    protected static $_pluginLoader = null;

    /**
     * @var string
     */
    protected $_name = null;

    /**
     * @var string
     */
    protected $_description = null;

    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Docblock_Tag $reflectionTag
     * @return Zend_CodeGenerator_Php_Docblock_Tag
     */
    public static function fromReflection(Zend_Reflection_Docblock_Tag $reflectionTag)
    {
        $tagName = $reflectionTag->getName();

        $codeGenDocblockTag = self::factory($tagName);

        // transport any properties via accessors and mutators from reflection to codegen object
        $reflectionClass = new ReflectionClass($reflectionTag);
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (substr($method->getName(), 0, 3) == 'get') {
                $propertyName = substr($method->getName(), 3);
                if (method_exists($codeGenDocblockTag, 'set' . $propertyName)) {
                    $codeGenDocblockTag->{'set' . $propertyName}($reflectionTag->{'get' . $propertyName}());
                }
            }
        }

        return $codeGenDocblockTag;
    }

    /**
     * setPluginLoader()
     *
     * @param Zend_Loader_PluginLoader $pluginLoader
     */
    public static function setPluginLoader(Zend_Loader_PluginLoader $pluginLoader)
    {
        self::$_pluginLoader = $pluginLoader;
        return;
    }

    /**
     * getPluginLoader()
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getPluginLoader()
    {
        if (self::$_pluginLoader == null) {
            #require_once 'Zend/Loader/PluginLoader.php';
            self::setPluginLoader(new Zend_Loader_PluginLoader(array(
                'Zend_CodeGenerator_Php_Docblock_Tag' => dirname(__FILE__) . '/Tag/'))
                );
        }

        return self::$_pluginLoader;
    }

    public static function factory($tagName)
    {
        $pluginLoader = self::getPluginLoader();

        try {
            $tagClass = $pluginLoader->load($tagName);
        } catch (Zend_Loader_Exception $exception) {
            $tagClass = 'Zend_CodeGenerator_Php_Docblock_Tag';
        }

        $tag = new $tagClass(array('name' => $tagName));
        return $tag;
    }

    /**
     * setName()
     *
     * @param string $name
     * @return Zend_CodeGenerator_Php_Docblock_Tag
     */
    public function setName($name)
    {
        $this->_name = ltrim($name, '@');
        return $this;
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * setDescription()
     *
     * @param string $description
     * @return Zend_CodeGenerator_Php_Docblock_Tag
     */
    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    /**
     * getDescription()
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        return '@' . $this->_name . ' ' . $this->_description;
    }

}