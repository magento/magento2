<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Class EavValidationRule
 */
class EavValidationRule
{
    /**
     * @var array
     */
    protected $validationRule = [
        'input_validation' => [
            'email' => ['validate-email' => true],
            'date' => ['validate-date' => true],
        ],
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
        $rules = [];
        if (isset($data['required']) && $data['required'] == 1) {
            $rules['required-entry'] = true;
        }
        $validation = $attribute->getValidateRules();
        if (!empty($validation)) {
            foreach ($validation as $type => $ruleName) {
                switch ($type) {
                    case 'input_validation':
                        if (isset($this->validationRule[$type][$ruleName])) {
                            $rules = array_merge($rules, $this->validationRule[$type][$ruleName]);
                        }
                        break;
                    case 'min_text_length':
                    case 'max_text_length':
                        $rules = array_merge($rules, [$type => $ruleName]);
                        break;
                }

            }
        }

        return $rules;
    }
}
