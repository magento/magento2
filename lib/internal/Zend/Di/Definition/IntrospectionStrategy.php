<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Di
 */

namespace Zend\Di\Definition;

use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser\GenericAnnotationParser;

/**
 * Strategy used to discover methods to be considered as endpoints for dependency injection based on implemented
 * interfaces, annotations and method names
 *
 * @category   Zend
 * @package    Zend_Di
 */
class IntrospectionStrategy
{
    /**
     * @var bool
     */
    protected $useAnnotations = false;

    /**
     * @var string[]
     */
    protected $methodNameInclusionPatterns = array('/^set[A-Z]{1}\w*/');

    /**
     * @var string[]
     */
    protected $interfaceInjectionInclusionPatterns = array('/\w*Aware\w*/');

    /**
     * @var AnnotationManager
     */
    protected $annotationManager = null;

    /**
     * Constructor
     *
     * @param null|AnnotationManager $annotationManager
     */
    public function __construct(AnnotationManager $annotationManager = null)
    {
        $this->annotationManager = ($annotationManager) ?: $this->createDefaultAnnotationManager();
    }

    /**
     * Get annotation manager
     *
     * @return null|AnnotationManager
     */
    public function getAnnotationManager()
    {
        return $this->annotationManager;
    }

    /**
     * Create default annotation manager
     *
     * @return AnnotationManager
     */
    public function createDefaultAnnotationManager()
    {
        $annotationManager = new AnnotationManager;
        $parser            = new GenericAnnotationParser();
        $parser->registerAnnotation(new Annotation\Inject());
        $annotationManager->attach($parser);

        return $annotationManager;
    }

    /**
     * set use annotations
     *
     * @param bool $useAnnotations
     */
    public function setUseAnnotations($useAnnotations)
    {
        $this->useAnnotations = (bool) $useAnnotations;
    }

    /**
     * Get use annotations
     *
     * @return bool
     */
    public function getUseAnnotations()
    {
        return $this->useAnnotations;
    }

    /**
     * Set method name inclusion pattern
     *
     * @param array $methodNameInclusionPatterns
     */
    public function setMethodNameInclusionPatterns(array $methodNameInclusionPatterns)
    {
        $this->methodNameInclusionPatterns = $methodNameInclusionPatterns;
    }

    /**
     * Get method name inclusion pattern
     *
     * @return array
     */
    public function getMethodNameInclusionPatterns()
    {
        return $this->methodNameInclusionPatterns;
    }

    /**
     * Set interface injection inclusion patterns
     *
     * @param array $interfaceInjectionInclusionPatterns
     */
    public function setInterfaceInjectionInclusionPatterns(array $interfaceInjectionInclusionPatterns)
    {
        $this->interfaceInjectionInclusionPatterns = $interfaceInjectionInclusionPatterns;
    }

    /**
     * Get interface injection inclusion patterns
     *
     * @return array
     */
    public function getInterfaceInjectionInclusionPatterns()
    {
        return $this->interfaceInjectionInclusionPatterns;
    }

}
