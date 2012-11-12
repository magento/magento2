<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Reflection;

use ReflectionProperty as PhpReflectionProperty;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Scanner\AnnotationScanner;
use Zend\Code\Scanner\CachingFileScanner;

/**
 * @todo       implement line numbers
 * @category   Zend
 * @package    Zend_Reflection
 */
class PropertyReflection extends PhpReflectionProperty implements ReflectionInterface
{
    protected $annotations;

    /**
     * Get declaring class reflection object
     *
     * @return ClassReflection
     */
    public function getDeclaringClass()
    {
        $phpReflection  = parent::getDeclaringClass();
        $zendReflection = new ClassReflection($phpReflection->getName());
        unset($phpReflection);
        return $zendReflection;
    }

    /**
     * Get DocBlock comment
     *
     * @return string|false False if no DocBlock defined
     */
    public function getDocComment()
    {
        return parent::getDocComment();
    }

    /**
     * @return false|DocBlockReflection
     */
    public function getDocBlock()
    {
        if (!($docComment = $this->getDocComment())) {
            return false;
        }
        $r = new DocBlockReflection($docComment);
        return $r;
    }

    /**
     * @param AnnotationManager $annotationManager
     * @return AnnotationCollection
     */
    public function getAnnotations(AnnotationManager $annotationManager)
    {
        if (null !== $this->annotations) {
            return $this->annotations;
        }

        if (($docComment = $this->getDocComment()) == '') {
            return false;
        }

        $class              = $this->getDeclaringClass();
        $cachingFileScanner = new CachingFileScanner($class->getFileName());
        $nameInformation    = $cachingFileScanner->getClassNameInformation($class->getName());
        $this->annotations  = new AnnotationScanner($annotationManager, $docComment, $nameInformation);

        return $this->annotations;
    }

    public function toString()
    {
        return $this->__toString();
    }
}
