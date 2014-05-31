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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Class.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Reflection_Property
 */
#require_once 'Zend/Reflection/Property.php';

/**
 * @see Zend_Reflection_Method
 */
#require_once 'Zend/Reflection/Method.php';

/**
 * Zend_Reflection_Docblock
 */
#require_once 'Zend/Reflection/Docblock.php';

/**
 * @category   Zend
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Reflection_Class extends ReflectionClass
{
    /**
     * Return the reflection file of the declaring file.
     *
     * @return Zend_Reflection_File
     */
    public function getDeclaringFile($reflectionClass = 'Zend_Reflection_File')
    {
        $instance = new $reflectionClass($this->getFileName());
        if (!$instance instanceof Zend_Reflection_File) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Invalid reflection class specified; must extend Zend_Reflection_File');
        }
        return $instance;
    }

    /**
     * Return the classes Docblock reflection object
     *
     * @param  string $reflectionClass Name of reflection class to use
     * @return Zend_Reflection_Docblock
     * @throws Zend_Reflection_Exception for missing docblock or invalid reflection class
     */
    public function getDocblock($reflectionClass = 'Zend_Reflection_Docblock')
    {
        if ('' == $this->getDocComment()) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception($this->getName() . ' does not have a docblock');
        }

        $instance = new $reflectionClass($this);
        if (!$instance instanceof Zend_Reflection_Docblock) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Invalid reflection class specified; must extend Zend_Reflection_Docblock');
        }
        return $instance;
    }

    /**
     * Return the start line of the class
     *
     * @param bool $includeDocComment
     * @return int
     */
    public function getStartLine($includeDocComment = false)
    {
        if ($includeDocComment) {
            if ($this->getDocComment() != '') {
                return $this->getDocblock()->getStartLine();
            }
        }

        return parent::getStartLine();
    }

    /**
     * Return the contents of the class
     *
     * @param bool $includeDocblock
     * @return string
     */
    public function getContents($includeDocblock = true)
    {
        $filename  = $this->getFileName();
        $filelines = file($filename);
        $startnum  = $this->getStartLine($includeDocblock);
        $endnum    = $this->getEndLine() - $this->getStartLine();

        return implode('', array_splice($filelines, $startnum, $endnum, true));
    }

    /**
     * Get all reflection objects of implemented interfaces
     *
     * @param  string $reflectionClass Name of reflection class to use
     * @return array Array of Zend_Reflection_Class
     */
    public function getInterfaces($reflectionClass = 'Zend_Reflection_Class')
    {
        $phpReflections  = parent::getInterfaces();
        $zendReflections = array();
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance = new $reflectionClass($phpReflection->getName());
            if (!$instance instanceof Zend_Reflection_Class) {
                #require_once 'Zend/Reflection/Exception.php';
                throw new Zend_Reflection_Exception('Invalid reflection class specified; must extend Zend_Reflection_Class');
            }
            $zendReflections[] = $instance;
            unset($phpReflection);
        }
        unset($phpReflections);
        return $zendReflections;
    }

    /**
     * Return method reflection by name
     *
     * @param  string $name
     * @param  string $reflectionClass Reflection class to utilize
     * @return Zend_Reflection_Method
     */
    public function getMethod($name, $reflectionClass = 'Zend_Reflection_Method')
    {
        $phpReflection  = parent::getMethod($name);
        $zendReflection = new $reflectionClass($this->getName(), $phpReflection->getName());

        if (!$zendReflection instanceof Zend_Reflection_Method) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Invalid reflection class specified; must extend Zend_Reflection_Method');
        }

        unset($phpReflection);
        return $zendReflection;
    }

    /**
     * Get reflection objects of all methods
     *
     * @param  string $filter
     * @param  string $reflectionClass Reflection class to use for methods
     * @return array Array of Zend_Reflection_Method objects
     */
    public function getMethods($filter = -1, $reflectionClass = 'Zend_Reflection_Method')
    {
        $phpReflections  = parent::getMethods($filter);
        $zendReflections = array();
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance = new $reflectionClass($this->getName(), $phpReflection->getName());
            if (!$instance instanceof Zend_Reflection_Method) {
                #require_once 'Zend/Reflection/Exception.php';
                throw new Zend_Reflection_Exception('Invalid reflection class specified; must extend Zend_Reflection_Method');
            }
            $zendReflections[] = $instance;
            unset($phpReflection);
        }
        unset($phpReflections);
        return $zendReflections;
    }

    /**
     * Get parent reflection class of reflected class
     *
     * @param  string $reflectionClass Name of Reflection class to use
     * @return Zend_Reflection_Class
     */
    public function getParentClass($reflectionClass = 'Zend_Reflection_Class')
    {
        $phpReflection = parent::getParentClass();
        if ($phpReflection) {
            $zendReflection = new $reflectionClass($phpReflection->getName());
            if (!$zendReflection instanceof Zend_Reflection_Class) {
                #require_once 'Zend/Reflection/Exception.php';
                throw new Zend_Reflection_Exception('Invalid reflection class specified; must extend Zend_Reflection_Class');
            }
            unset($phpReflection);
            return $zendReflection;
        } else {
            return false;
        }
    }

    /**
     * Return reflection property of this class by name
     *
     * @param  string $name
     * @param  string $reflectionClass Name of reflection class to use
     * @return Zend_Reflection_Property
     */
    public function getProperty($name, $reflectionClass = 'Zend_Reflection_Property')
    {
        $phpReflection  = parent::getProperty($name);
        $zendReflection = new $reflectionClass($this->getName(), $phpReflection->getName());
        if (!$zendReflection instanceof Zend_Reflection_Property) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Invalid reflection class specified; must extend Zend_Reflection_Property');
        }
        unset($phpReflection);
        return $zendReflection;
    }

    /**
     * Return reflection properties of this class
     *
     * @param  int $filter
     * @param  string $reflectionClass Name of reflection class to use
     * @return array Array of Zend_Reflection_Property
     */
    public function getProperties($filter = -1, $reflectionClass = 'Zend_Reflection_Property')
    {
        $phpReflections = parent::getProperties($filter);
        $zendReflections = array();
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance = new $reflectionClass($this->getName(), $phpReflection->getName());
            if (!$instance instanceof Zend_Reflection_Property) {
                #require_once 'Zend/Reflection/Exception.php';
                throw new Zend_Reflection_Exception('Invalid reflection class specified; must extend Zend_Reflection_Property');
            }
            $zendReflections[] = $instance;
            unset($phpReflection);
        }
        unset($phpReflections);
        return $zendReflections;
    }
}
