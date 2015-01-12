<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Template constructions filter
 */
namespace Magento\Framework\Filter;

class Template implements \Zend_Filter_Interface
{
    /**
     * Construction regular expression
     */
    const CONSTRUCTION_PATTERN = '/{{([a-z]{0,10})(.*?)}}/si';

    /**#@+
     * Construction logic regular expression
     */
    const CONSTRUCTION_DEPEND_PATTERN = '/{{depend\s*(.*?)}}(.*?){{\\/depend\s*}}/si';

    const CONSTRUCTION_IF_PATTERN = '/{{if\s*(.*?)}}(.*?)({{else}}(.*?))?{{\\/if\s*}}/si';

    /**#@-*/

    /**
     * Assigned template variables
     *
     * @var array
     */
    protected $_templateVars = [];

    /**
     * Include processor
     *
     * @var callable|null
     */
    protected $_includeProcessor = null;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @param \Magento\Framework\Stdlib\String $string
     * @param array $variables
     */
    public function __construct(\Magento\Framework\Stdlib\String $string, $variables = [])
    {
        $this->string = $string;
        $this->setVariables($variables);
    }

    /**
     * Sets template variables that's can be called through {var ...} statement
     *
     * @param array $variables
     * @return \Magento\Framework\Filter\Template
     */
    public function setVariables(array $variables)
    {
        foreach ($variables as $name => $value) {
            $this->_templateVars[$name] = $value;
        }
        return $this;
    }

    /**
     * Sets the processor of includes.
     *
     * @param callable $callback it must return string
     * @return $this
     */
    public function setIncludeProcessor(array $callback)
    {
        $this->_includeProcessor = $callback;
        return $this;
    }

    /**
     * Sets the processor of includes.
     *
     * @return callable|null
     */
    public function getIncludeProcessor()
    {
        return is_callable($this->_includeProcessor) ? $this->_includeProcessor : null;
    }

    /**
     * Filter the string as template.
     *
     * @param string $value
     * @return string
     * @throws \Exception
     */
    public function filter($value)
    {
        // "depend" and "if" operands should be first
        foreach ([
                     self::CONSTRUCTION_DEPEND_PATTERN => 'dependDirective',
                     self::CONSTRUCTION_IF_PATTERN => 'ifDirective',
                 ] as $pattern => $directive) {
            if (preg_match_all($pattern, $value, $constructions, PREG_SET_ORDER)) {
                foreach ($constructions as $construction) {
                    $callback = [$this, $directive];
                    if (!is_callable($callback)) {
                        continue;
                    }
                    try {
                        $replacedValue = call_user_func($callback, $construction);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                    $value = str_replace($construction[0], $replacedValue, $value);
                }
            }
        }

        if (preg_match_all(self::CONSTRUCTION_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                $callback = [$this, $construction[1] . 'Directive'];
                if (!is_callable($callback)) {
                    continue;
                }
                try {
                    $replacedValue = call_user_func($callback, $construction);
                } catch (\Exception $e) {
                    throw $e;
                }
                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }
        return $value;
    }

    /**
     * @param string[] $construction
     * @return string
     */
    public function varDirective($construction)
    {
        if (count($this->_templateVars) == 0) {
            // If template prepossessing
            return $construction[0];
        }

        $replacedValue = $this->_getVariable($construction[2], '');
        return $replacedValue;
    }

    /**
     * @param string[] $construction
     * @return mixed
     */
    public function includeDirective($construction)
    {
        // Processing of {include template=... [...]} statement
        $includeParameters = $this->_getIncludeParameters($construction[2]);
        if (!isset($includeParameters['template']) or !$this->getIncludeProcessor()) {
            // Not specified template or not set include processor
            $replacedValue = '{Error in include processing}';
        } else {
            // Including of template
            $templateCode = $includeParameters['template'];
            unset($includeParameters['template']);
            $includeParameters = array_merge_recursive($includeParameters, $this->_templateVars);
            $replacedValue = call_user_func($this->getIncludeProcessor(), $templateCode, $includeParameters);
        }
        return $replacedValue;
    }

    /**
     * @param string[] $construction
     * @return string
     */
    public function dependDirective($construction)
    {
        if (count($this->_templateVars) == 0) {
            // If template processing
            return $construction[0];
        }

        if ($this->_getVariable($construction[1], '') == '') {
            return '';
        } else {
            return $construction[2];
        }
    }

    /**
     * @param string[] $construction
     * @return string
     */
    public function ifDirective($construction)
    {
        if (count($this->_templateVars) == 0) {
            return $construction[0];
        }

        if ($this->_getVariable($construction[1], '') == '') {
            if (isset($construction[3]) && isset($construction[4])) {
                return $construction[4];
            }
            return '';
        } else {
            return $construction[2];
        }
    }

    /**
     * Return associative array of include construction.
     *
     * @param string $value raw parameters
     * @return array
     */
    protected function _getIncludeParameters($value)
    {
        $tokenizer = new \Magento\Framework\Filter\Template\Tokenizer\Parameter();
        $tokenizer->setString($value);
        $params = $tokenizer->tokenize();
        foreach ($params as $key => $value) {
            if (substr($value, 0, 1) === '$') {
                $params[$key] = $this->_getVariable(substr($value, 1), null);
            }
        }
        return $params;
    }

    /**
     * Return variable value for var construction
     *
     * @param string $value raw parameters
     * @param string $default default value
     * @return string
     */
    protected function _getVariable($value, $default = '{no_value_defined}')
    {
        \Magento\Framework\Profiler::start('email_template_processing_variables');
        $tokenizer = new \Magento\Framework\Filter\Template\Tokenizer\Variable();
        $tokenizer->setString($value);
        $stackVars = $tokenizer->tokenize();
        $result = $default;
        $last = 0;
        for ($i = 0; $i < count($stackVars); $i++) {
            if ($i == 0 && isset($this->_templateVars[$stackVars[$i]['name']])) {
                // Getting of template value
                $stackVars[$i]['variable'] = & $this->_templateVars[$stackVars[$i]['name']];
            } elseif (isset(
                $stackVars[$i - 1]['variable']
                ) && $stackVars[$i - 1]['variable'] instanceof \Magento\Framework\Object
            ) {
                // If object calling methods or getting properties
                if ($stackVars[$i]['type'] == 'property') {
                    $caller = 'get' . $this->string->upperCaseWords($stackVars[$i]['name'], '_', '');
                    $stackVars[$i]['variable'] = method_exists(
                        $stackVars[$i - 1]['variable'],
                        $caller
                    ) ? $stackVars[$i - 1]['variable']->{$caller}() : $stackVars[$i - 1]['variable']->getData(
                        $stackVars[$i]['name']
                    );
                } elseif ($stackVars[$i]['type'] == 'method') {
                    // Calling of object method
                    if (method_exists(
                        $stackVars[$i - 1]['variable'],
                        $stackVars[$i]['name']
                    ) || substr(
                        $stackVars[$i]['name'],
                        0,
                        3
                    ) == 'get'
                    ) {
                        $stackVars[$i]['variable'] = call_user_func_array(
                            [$stackVars[$i - 1]['variable'], $stackVars[$i]['name']],
                            $stackVars[$i]['args']
                        );
                    }
                }
                $last = $i;
            }
        }

        if (isset($stackVars[$last]['variable'])) {
            // If value for construction exists set it
            $result = $stackVars[$last]['variable'];
        }
        \Magento\Framework\Profiler::stop('email_template_processing_variables');
        return $result;
    }
}
