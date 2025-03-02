<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address\Sanitizer;

use Magento\Customer\Model\Address\SanitizerInterface;
use Magento\Customer\Model\Address\AbstractAddress;

class TemplateVars implements SanitizerInterface
{
    /**
     * Template vars patter
     * @var string
     */
    private string $templateVarsPattern = '/\{\{\s*([^{}]+)\s*\}\}/';

    /**
     * List of attributes that can be entered via user input or API
     * @var array|string[]
     */
    private array $attributesToSanitize = [
        'firstname',
        'lastname',
        'middlename',
        'city',
        'company',
        'country_id',
        'fax',
        'postcode',
        'prefix',
        'region',
        'region_id',
        'street',
        'suffix',
        'telephone',
        'vat_id'
    ];


    /**
     * Sanitize string for template vars in address attributes.
     *
     * @param AbstractAddress $address
     * @return AbstractAddress
     */
    public function sanitize(AbstractAddress $address): AbstractAddress
    {
        foreach ($this->attributesToSanitize as $attributeCode) {
            $attributeValue = $address->getData($attributeCode);
            preg_match_all($this->templateVarsPattern, (string)$attributeValue, $matches);
            if (!empty($matches[0])) {
                $sanitizedAttributeValue = $this->sanitizeTemplateVars($attributeValue);
                $address->setData($attributeCode, $sanitizedAttributeValue);
            }
        }

        return $address;
    }

    /**
     * Sanitize string for template vars in address attributes.
     *
     * @param string $value
     * @return string
     */
    private function sanitizeTemplateVars(string $value): string
    {
        if (!empty($value)) {
            return str_replace(['{', '}'], ['&#123;', '&#125;'], $value);
        }
        return $value;
    }

}
