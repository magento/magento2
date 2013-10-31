<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\MethodReflection;

class MethodGenerator extends AbstractMemberGenerator
{
    /**
     * @var DocBlockGenerator
     */
    protected $docBlock = null;

    /**
     * @var ParameterGenerator[]
     */
    protected $parameters = array();

    /**
     * @var string
     */
    protected $body = null;

    /**
     * @param  MethodReflection $reflectionMethod
     * @return MethodGenerator
     */
    public static function fromReflection(MethodReflection $reflectionMethod)
    {
        $method = new static();

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

    /**
     * Generate from array
     *
     * @configkey name           string        [required] Class Name
     * @configkey docblock       string        The docblock information
     * @configkey flags          int           Flags, one of MethodGenerator::FLAG_ABSTRACT MethodGenerator::FLAG_FINAL
     * @configkey parameters     string        Class which this class is extending
     * @configkey body           string
     * @configkey abstract       bool
     * @configkey final          bool
     * @configkey static         bool
     * @configkey visibility     string
     *
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return MethodGenerator
     */
    public static function fromArray(array $array)
    {
        if (!isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Method generator requires that a name is provided for this object'
            );
        }

        $method = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(array('.', '-', '_'), '', $name))) {
                case 'docblock':
                    $docBlock = ($value instanceof DocBlockGenerator) ? $value : DocBlockGenerator::fromArray($value);
                    $method->setDocBlock($docBlock);
                    break;
                case 'flags':
                    $method->setFlags($value);
                    break;
                case 'parameters':
                    $method->setParameters($value);
                    break;
                case 'body':
                    $method->setBody($value);
                    break;
                case 'abstract':
                    $method->setAbstract($value);
                    break;
                case 'final':
                    $method->setFinal($value);
                    break;
                case 'static':
                    $method->setStatic($value);
                    break;
                case 'visibility':
                    $method->setVisibility($value);
                    break;
            }
        }

        return $method;
    }

    /**
     * @param  string $name
     * @param  array $parameters
     * @param  int|array $flags
     * @param  string $body
     * @param  DocBlockGenerator|string $docBlock
     */
    public function __construct($name = null, array $parameters = array(), $flags = self::FLAG_PUBLIC, $body = null,
                                $docBlock = null)
    {
        if ($name) {
            $this->setName($name);
        }
        if ($parameters) {
            $this->setParameters($parameters);
        }
        if ($flags !== self::FLAG_PUBLIC) {
            $this->setFlags($flags);
        }
        if ($body) {
            $this->setBody($body);
        }
        if ($docBlock) {
            $this->setDocBlock($docBlock);
        }
    }

    /**
     * @param  array $parameters
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
     * @param  ParameterGenerator|string $parameter
     * @throws Exception\InvalidArgumentException
     * @return MethodGenerator
     */
    public function setParameter($parameter)
    {
        if (is_string($parameter)) {
            $parameter = new ParameterGenerator($parameter);
        } elseif (!$parameter instanceof ParameterGenerator) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s is expecting either a string, array or an instance of %s\ParameterGenerator',
                __METHOD__,
                __NAMESPACE__
            ));
        }

        $parameterName = $parameter->getName();

        $this->parameters[$parameterName] = $parameter;

        return $this;
    }

    /**
     * @return ParameterGenerator[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param  string $body
     * @return MethodGenerator
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
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

        $output .= ')';

        if ($this->isAbstract()) {
            return $output . ';';
        }

        $output .= self::LINE_FEED . $indent . '{' . self::LINE_FEED;

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
