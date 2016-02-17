<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

class ArrayInput extends Input
{
    /**
     * @var array
     */
    protected $value = array();

    /**
     * @param  array $value
     * @throws Exception\InvalidArgumentException
     * @return Input
     */
    public function setValue($value)
    {
        if (!is_array($value)) {
            throw new Exception\InvalidArgumentException(
                sprintf('Value must be an array, %s given.', gettype($value))
            );
        }
        return parent::setValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function resetValue()
    {
        $this->value = array();
        $this->hasValue = false;
        return $this;
    }

    /**
     * @return array
     */
    public function getValue()
    {
        $filter = $this->getFilterChain();
        $result = array();
        foreach ($this->value as $key => $value) {
            $result[$key] = $filter->filter($value);
        }
        return $result;
    }

    /**
     * @param  mixed $context Extra "context" to provide the validator
     * @return bool
     */
    public function isValid($context = null)
    {
        $hasValue = $this->hasValue();
        $required = $this->isRequired();
        $hasFallback = $this->hasFallback();

        if (! $hasValue && $hasFallback) {
            $this->setValue($this->getFallbackValue());
            return true;
        }

        if (! $hasValue && $required) {
            if ($this->errorMessage === null) {
                $this->errorMessage = $this->prepareRequiredValidationFailureMessage();
            }
            return false;
        }

        if (!$this->continueIfEmpty() && !$this->allowEmpty()) {
            $this->injectNotEmptyValidator();
        }
        $validator = $this->getValidatorChain();
        $values    = $this->getValue();
        $result    = true;
        foreach ($values as $value) {
            $empty = ($value === null || $value === '' || $value === array());
            if ($empty && !$this->isRequired() && !$this->continueIfEmpty()) {
                $result = true;
                continue;
            }
            if ($empty && $this->allowEmpty() && !$this->continueIfEmpty()) {
                $result = true;
                continue;
            }
            $result = $validator->isValid($value, $context);
            if (!$result) {
                if ($hasFallback) {
                    $this->setValue($this->getFallbackValue());
                    return true;
                }
                break;
            }
        }

        return $result;
    }
}
