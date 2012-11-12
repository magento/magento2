<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Scanner;

use Zend\Code\NameInformation;

class ParameterScanner
{
    protected $isScanned = false;

    protected $declaringScannerClass = null;
    protected $declaringClass = null;
    protected $declaringScannerFunction = null;
    protected $declaringFunction = null;
    protected $defaultValue = null;
    protected $class = null;
    protected $name = null;
    protected $position = null;
    protected $isArray = false;
    protected $isDefaultValueAvailable = false;
    protected $isOptional = false;
    protected $isPassedByReference = false;

    protected $tokens = null;
    protected $nameInformation = null;

    public function __construct(array $parameterTokens, NameInformation $nameInformation = null)
    {
        $this->tokens          = $parameterTokens;
        $this->nameInformation = $nameInformation;
    }

    public function setDeclaringClass($class)
    {
        $this->declaringClass = $class;
    }

    public function setDeclaringScannerClass(ClassScanner $scannerClass)
    {
        $this->declaringScannerClass = $scannerClass;
    }

    public function setDeclaringFunction($function)
    {
        $this->declaringFunction = $function;
    }

    public function setDeclaringScannerFunction(MethodScanner $scannerFunction)
    {
        $this->declaringScannerFunction = $scannerFunction;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    protected function scan()
    {
        if ($this->isScanned) {
            return;
        }

        $tokens = &$this->tokens;

        reset($tokens);

        SCANNER_TOP:

        $token = current($tokens);

        if (is_string($token)) {
            // check pass by ref
            if ($token === '&') {
                $this->isPassedByReference = true;
                goto SCANNER_CONTINUE;
            }
            if ($token === '=') {
                $this->isOptional              = true;
                $this->isDefaultValueAvailable = true;
                goto SCANNER_CONTINUE;
            }
        } else {
            if ($this->name === null && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
                $this->class .= $token[1];
                goto SCANNER_CONTINUE;
            }
            if ($token[0] === T_VARIABLE) {
                $this->name = ltrim($token[1], '$');
                goto SCANNER_CONTINUE;
            }

        }

        if ($this->name !== null) {
            $this->defaultValue .= (is_string($token)) ? $token : $token[1];
        }


        SCANNER_CONTINUE:

        if (next($this->tokens) === false) {
            goto SCANNER_END;
        }
        goto SCANNER_TOP;

        SCANNER_END:

        if ($this->class && $this->nameInformation) {
            $this->class = $this->nameInformation->resolveName($this->class);
        }

        $this->isScanned = true;
    }

    /**
     * @return $declaringScannerClass
     */
    public function getDeclaringScannerClass()
    {
        return $this->declaringScannerClass;
    }

    /**
     * @return $declaringClass
     */
    public function getDeclaringClass()
    {
        return $this->declaringClass;
    }

    /**
     * @return $declaringScannerFunction
     */
    public function getDeclaringScannerFunction()
    {
        return $this->declaringScannerFunction;
    }

    /**
     * @return $declaringFunction
     */
    public function getDeclaringFunction()
    {
        return $this->declaringFunction;
    }

    /**
     * @return $defaultValue
     */
    public function getDefaultValue()
    {
        $this->scan();
        return $this->defaultValue;
    }

    /**
     * @return $class
     */
    public function getClass()
    {
        $this->scan();
        return $this->class;
    }

    /**
     * @return $name
     */
    public function getName()
    {
        $this->scan();
        return $this->name;
    }

    /**
     * @return $position
     */
    public function getPosition()
    {
        $this->scan();
        return $this->position;
    }

    /**
     * @return boolean
     */
    public function isArray()
    {
        $this->scan();
        return $this->isArray;
    }

    /**
     * @return boolean
     */
    public function isDefaultValueAvailable()
    {
        $this->scan();
        return $this->isDefaultValueAvailable;
    }

    /**
     * @return boolean
     */
    public function isOptional()
    {
        $this->scan();
        return $this->isOptional;
    }

    /**
     * @return boolean
     */
    public function isPassedByReference()
    {
        $this->scan();
        return $this->isPassedByReference;
    }


}
