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

/**
 * @see Zend_Reflection_Class
 */
#require_once 'Zend/Reflection/Class.php';

/**
 * @see Zend_Reflection_Docblock
 */
#require_once 'Zend/Reflection/Docblock.php';

/**
 * @see Zend_Reflection_Parameter
 */
#require_once 'Zend/Reflection/Parameter.php';

/**
 * @category   Zend
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Reflection_Method extends ReflectionMethod
{
    /**
     * Retrieve method docblock reflection
     *
     * @return Zend_Reflection_Docblock
     * @throws Zend_Reflection_Exception
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
            throw new Zend_Reflection_Exception('Invalid reflection class provided; must extend Zend_Reflection_Docblock');
        }
        return $instance;
    }

    /**
     * Get start line (position) of method
     *
     * @param  bool $includeDocComment
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
     * Get reflection of declaring class
     *
     * @param  string $reflectionClass Name of reflection class to use
     * @return Zend_Reflection_Class
     */
    public function getDeclaringClass($reflectionClass = 'Zend_Reflection_Class')
    {
        $phpReflection  = parent::getDeclaringClass();
        $zendReflection = new $reflectionClass($phpReflection->getName());
        if (!$zendReflection instanceof Zend_Reflection_Class) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Invalid reflection class provided; must extend Zend_Reflection_Class');
        }
        unset($phpReflection);
        return $zendReflection;
    }

    /**
     * Get all method parameter reflection objects
     *
     * @param  string $reflectionClass Name of reflection class to use
     * @return array of Zend_Reflection_Parameter objects
     */
    public function getParameters($reflectionClass = 'Zend_Reflection_Parameter')
    {
        $phpReflections  = parent::getParameters();
        $zendReflections = array();
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance = new $reflectionClass(array($this->getDeclaringClass()->getName(), $this->getName()), $phpReflection->getName());
            if (!$instance instanceof Zend_Reflection_Parameter) {
                #require_once 'Zend/Reflection/Exception.php';
                throw new Zend_Reflection_Exception('Invalid reflection class provided; must extend Zend_Reflection_Parameter');
            }
            $zendReflections[] = $instance;
            unset($phpReflection);
        }
        unset($phpReflections);
        return $zendReflections;
    }

    /**
     * Get method contents
     *
     * @param  bool $includeDocblock
     * @return string
     */
    public function getContents($includeDocblock = true)
    {
        $fileContents = file($this->getFileName());
        $startNum = $this->getStartLine($includeDocblock);
        $endNum = ($this->getEndLine() - $this->getStartLine());

        return implode("\n", array_splice($fileContents, $startNum, $endNum, true));
    }

    /**
     * Get method body
     *
     * @return string
     */
    public function getBody()
    {
        $lines = array_slice(
            file($this->getDeclaringClass()->getFileName(), FILE_IGNORE_NEW_LINES),
            $this->getStartLine()-1,
            ($this->getEndLine() - $this->getStartLine()) + 1,
            true
        );

        // Strip off lines until we come to a closing bracket
        do {
            if (count($lines) == 0) break;
            $firstLine = array_shift($lines);
        } while (strpos($firstLine, ')') === false);

        // If the opening brace isn't on the same line as method
        // signature, then we should pop off more lines until we find it
        if (strpos($firstLine,'{') === false) {
            do {
                if (count($lines) == 0) break;
                $firstLine = array_shift($lines);
            } while (strpos($firstLine, '{') === false);
        }

        // If there are more characters on the line after the opening brace,
        // push them back onto the lines stack as they are part of the body
        $restOfFirstLine = trim(substr($firstLine, strpos($firstLine, '{')+1));
        if (!empty($restOfFirstLine)) {
            array_unshift($lines, $restOfFirstLine);
        }

        $lastLine = array_pop($lines);

        // If there are more characters on the line before the closing brace,
        // push them back onto the lines stack as they are part of the body
        $restOfLastLine = trim(substr($lastLine, 0, strrpos($lastLine, '}')-1));
        if (!empty($restOfLastLine)) {
            array_push($lines, $restOfLastLine);
        }

        // just in case we had code on the bracket lines
        return implode("\n", $lines);
    }
}
