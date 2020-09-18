<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Validator\Attribute;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Options
 *
 * Validation of EAV attribute options value
 */
class Options extends AbstractValidator
{
    /**
     * Validation pattern for attribute options HTML tags
     */
    private const VALIDATION_HTML_TAGS_RULE_PATTERN = '/<[^<]+>/';

    /**
     * Validation message for attribute options with HTML tags
     */
    private const VALIDATION_HTML_TAGS_RULE_MESSAGE = 'HTML tags are not allowed for the attribute options. ' .
        'Those have been found in option "%1"';

    /**
     * Validates the correctness of the attribute options value
     *
     * @param array $options
     *
     * @return bool
     */
    public function isValid($options): bool
    {
        $errorMessages = [];

        foreach ($options as $optionValues) {
            if (!is_array($optionValues)) {
                $optionValues = [$optionValues];
            }
            foreach ($optionValues as $optionValue) {
                if (!$optionValue) {
                    continue;
                }

                if ($this->hasOptionValueHtmlTags($optionValue)) {
                    $errorMessages[] = __(
                        static::VALIDATION_HTML_TAGS_RULE_MESSAGE,
                        $optionValue
                    );
                }
            }
        }

        $this->_addMessages($errorMessages);

        return !$this->hasMessages();
    }

    /**
     * Checks whenever there are HTML tags in the given option value
     *
     * @param string $optionValue
     *
     * @return bool
     */
    private function hasOptionValueHtmlTags(string $optionValue): bool
    {
        return (bool) preg_match(self::VALIDATION_HTML_TAGS_RULE_PATTERN, $optionValue);
    }
}
