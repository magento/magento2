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
 * @version    $Id: Function.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Reflection_Parameter
 */
#require_once 'Zend/Reflection/Parameter.php';

/**
 * @category   Zend
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Reflection_Function extends ReflectionFunction
{
    /**
     * Get function docblock
     *
     * @param  string $reflectionClass Name of reflection class to use
     * @return Zend_Reflection_Docblock
     */
    public function getDocblock($reflectionClass = 'Zend_Reflection_Docblock')
    {
        if ('' == ($comment = $this->getDocComment())) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception($this->getName() . ' does not have a docblock');
        }
        $instance = new $reflectionClass($comment);
        if (!$instance instanceof Zend_Reflection_Docblock) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Invalid reflection class provided; must extend Zend_Reflection_Docblock');
        }
        return $instance;
    }

    /**
     * Get start line (position) of function
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
     * Get contents of function
     *
     * @param  bool $includeDocblock
     * @return string
     */
    public function getContents($includeDocblock = true)
    {
        return implode("\n",
            array_splice(
                file($this->getFileName()),
                $this->getStartLine($includeDocblock),
                ($this->getEndLine() - $this->getStartLine()),
                true
                )
            );
    }

    /**
     * Get function parameters
     *
     * @param  string $reflectionClass Name of reflection class to use
     * @return array Array of Zend_Reflection_Parameter
     */
    public function getParameters($reflectionClass = 'Zend_Reflection_Parameter')
    {
        $phpReflections  = parent::getParameters();
        $zendReflections = array();
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance = new $reflectionClass($this->getName(), $phpReflection->getName());
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
     * Get return type tag
     *
     * @return Zend_Reflection_Docblock_Tag_Return
     */
    public function getReturn()
    {
        $docblock = $this->getDocblock();
        if (!$docblock->hasTag('return')) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Function does not specify an @return annotation tag; cannot determine return type');
        }
        $tag    = $docblock->getTag('return');
        $return = Zend_Reflection_Docblock_Tag::factory('@return ' . $tag->getDescription());
        return $return;
    }
}
