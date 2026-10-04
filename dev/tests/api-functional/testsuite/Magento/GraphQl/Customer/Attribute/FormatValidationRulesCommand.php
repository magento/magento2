<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer\Attribute;

/**
 * Command to format validation rules appropriately for testing purposes.
 */
class FormatValidationRulesCommand
{
    /**
     * Execute command to format validation rules provided as argument.
     *
     * @param array $validationRules
     * @return array
     */
    public function execute(array $validationRules): array
    {
        $formattedValidationRules = [];
        foreach ($validationRules as $key => $value) {
            $formattedValidationRules[] = ['name' => $key, 'value' => $value];
        }
        return $formattedValidationRules;
    }
}
