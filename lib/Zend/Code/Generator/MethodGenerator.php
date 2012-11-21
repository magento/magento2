<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\MethodReflection;

/**
 * @category   Zend
 * @package    Zend_Code_Generator
 */
class MethodGenerator extends AbstractMemberGenerator
{
    /**
     * @var DocBlockGenerator
     */
    protected $docBlock = null;

    /**
     * @var bool
     */
    protected $isFinal = false;

    /** @var ParameterGenerator[] */
    protected $parameters = array();

    /**
     * @var string
     */
    protected $body = null;

    /**
     * fromReflection()
     *
     * @param  MethodReflection $reflectionMethod
     * @return MethodGenerator
     */
    public static function fromReflection(MethodReflection $reflectionMethod)
    {
        $method = new self();

        $method->setSourceContent($reflectionMethod->getContents(false));
        $method->setSourceDirty(false);

        if ($reflectionMethod->getDocComment() != '') {
            $method->setDocBlock(DocBlockGenerator::fromReflection($reflectionMethod->getDocBlock()));
        }

        $method->setFinal($reflectionMethod->isFinal());

        if ($reflectionMethod->isPrivate()) {
            $method->setVisibility(self::VISIBILITY_PRIVATE);
        } elseif ($reflectionMethod->isProtected()) {
            $method->setVisibility(self::VISIBILITY_PROTECTED);
        } else {
            $method->setVisibility(self::VISIBILITY_PUBLIC);
        }

        $method->setStatic($reflectionMethod->isStatic());

        $method->setName($reflectionMethod->getName());

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $method->setParameter(ParameterGenerator::fromReflection($reflectionParameter));
        }

        $method->setBody($reflectionMethod->getBody());

        return $method;
    }

    public function __construct($name = null, array $parameters = array(), $flags = self::FLAG_PUBLIC, $body = null,
                                $docBlock = null)
    {
        if ($name !== null) {
            $this->setName($name);
        }
        if ($parameters !== array()) {
            $this->setParameters($parameters);
        }
        if ($flags !== self::FLAG_PUBLIC) {
            $this->setFlags($flags);
        }
        if ($body !== null) {
            $this->setBody($body);
        }
        if ($docBlock !== null) {
            $this->setDocBlock($docBlock);
        }
    }

    /**
     * setParameters()
     *
     * @param array $parameters
     * @return MethodGenerator
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->setParameter($parameter);
        }
        return $this;
    }

    /**
     * setParameter()
     *
     * @param ParameterGenerator|string $parameter
     * @throws Exception\InvalidArgumentException
     * @return MethodGenerator
     */
    public function setParameter($parameter)
    {
        if (is_string($parameter)) {
            $parameter = new ParameterGenerator($parameter);
        } elseif (!$parameter instanceof ParameterGenerator) {
            throw new Exception\InvalidArgumentException(
                'setParameter() is expecting either a string, array or an instance of Zend\Code\Generator\ParameterGenerator'
            );
        }
        $parameterName = $parameter->getName();

        $this->parameters[$parameterName] = $parameter;
        return $this;
    }

    /**
     * getParameters()
     *
     * @return ParameterGenerator[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * setBody()
     *
     * @param string $body
     * @return MethodGenerator
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * getBody()
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '';

        $indent = $this->getIndentation();

        if (($docBlock = $this->getDocBlock()) !== null) {
            $docBlock->setIndentation($indent);
            $output .= $docBlock->generate();
        }

        $output .= $indent;

        if ($this->isAbstract()) {
            $output .= 'abstract ';
        } else {
            $output .= (($this->isFinal()) ? 'final ' : '');
        }

        $output .= $this->getVisibility()
            . (($this->isStatic()) ? ' static' : '')
            . ' function ' . $this->getName() . '(';

        $parameters = $this->getParameters();
        if (!empty($parameters)) {
            foreach ($parameters as $parameter) {
                $parameterOutput[] = $parameter->generate();
            }

            $output .= implode(', ', $parameterOutput);
        }

        $output .= ')' . self::LINE_FEED . $indent . '{' . self::LINE_FEED;

        if ($this->body) {
            $output .= preg_replace('#^(.+?)$#m', $indent . $indent . '$1', trim($this->body))
                . self::LINE_FEED;
        }

        $output .= $indent . '}' . self::LINE_FEED;

        return $output;
    }

    public function __toString()
    {
        return $this->generate();
    }

}
