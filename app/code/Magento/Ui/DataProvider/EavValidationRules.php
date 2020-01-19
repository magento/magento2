<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\DataProvider;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * @api
 * @since 100.0.2
 */
class EavValidationRules
{
    /**
     * @var array
     * @since 100.0.6
     */
    protected $validationRules = [
        'email' => ['validate-email' => true],
        'date' => ['validate-date' => true],
    ];

    /**
     * Build validation rules
     *
     * @param AbstractAttribute $attribute
     * @param array $data
     * @return array
     */
    public function build(AbstractAttribute $attribute, array $data)
    {
        $validations = [];
        if (isset($data['required']) && $data['required'] == 1) {
            $validations = array_merge($validations, ['required-entry' => true]);
        }
        if ($attribute->getFrontendInput() === 'price') {
            $validations = array_merge($validations, ['validate-zero-or-greater' => true]);
        }
        if ($attribute->getValidateRules()) {
            $validations = array_merge($validations, $this->clipLengthRules($attribute->getValidateRules()));
        }
        return $this->aggregateRules($validations);
    }

    /**
     * @param array $validations
     * @return array
     */
    private function aggregateRules(array $validations): array
    {
        $rules = [];
        foreach ($validations as $type => $ruleValue) {
            $rule = [$type => $ruleValue];
            if ($type === 'input_validation') {
                $rule = $this->validationRules[$ruleValue] ?? [];
            }
            if (count($rule) !== 0) {
                $key = key($rule);
                $rules[$key] = $rule[$key];
            }
        }
        return $rules;
    }

    /**
     * @param array $rules
     * @return array
     */
    private function clipLengthRules(array $rules): array
    {
        if (empty($rules['input_validation'])) {
            unset(
                $rules['min_text_length'],
                $rules['max_text_length']
            );
        }
        return $rules;
    }
}
