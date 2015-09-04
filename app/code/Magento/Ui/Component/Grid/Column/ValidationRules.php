<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Grid\Column;

use Magento\Customer\Api\Data\ValidationRuleInterface;

class ValidationRules
{
    /**
     * @var array
     */
    protected $inputValidationMap = [
        'alpha' => 'validate-alpha',
        'numeric' => 'validate-number',
        'alphanumeric' => 'validate-alphanum',
        'url' => 'validate-url',
        'email' => 'validate-email',
    ];

    /**
     * @param $isRequired
     * @param $validationRules
     * @return array
     */
    public function getValidationRules($isRequired, $validationRules)
    {
        $rules = [];
        if ($isRequired) {
            $rules['required-entry'] = true;
        }
        if (empty($validationRules)) {
            return $rules;
        }
        /** @var ValidationRuleInterface $rule */
        foreach ($validationRules as $rule) {
            if (!$rule instanceof ValidationRuleInterface) {
                continue;
            }
            $validationClass = $this->getValidationClass($rule);
            if ($validationClass) {
                $rules[$validationClass] = $this->getRuleValue($rule);
            }
        }

        return $rules;
    }

    /**
     * @param ValidationRuleInterface $rule
     * @return string
     */
    protected function getValidationClass(ValidationRuleInterface $rule)
    {
        $key = $rule->getName() == 'input_validation' ? $rule->getValue() : $rule->getName();
        return isset($this->inputValidationMap[$key])
            ? $this->inputValidationMap[$key]
            : $key;
    }

    /**
     * @param ValidationRuleInterface $rule
     * @return bool|string
     */
    protected function getRuleValue(ValidationRuleInterface $rule)
    {
        return $rule->getName() != 'input_validation' ? $rule->getValue() : true;
    }
}
