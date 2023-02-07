<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Validator\RegexFactory;

class RegexValidator extends RegexFactory
{

    /**
     * @var RegexFactory
     */
    private RegexFactory $regexValidatorFactory;

    /**
     * Validation pattern for handles array
     */
    private const VALIDATION_RULE_PATTERN = '/^[a-z0-9,.]+[a-z0-9_,.]*$/i';

    /**
     * @param RegexFactory|null $regexValidatorFactory
     */
    public function __construct(
        ?RegexFactory $regexValidatorFactory = null
    ) {
        $this->regexValidatorFactory = $regexValidatorFactory
            ?: ObjectManager::getInstance()->get(RegexFactory::class);
    }

    /**
     * Validates parameter regex
     *
     * @param string $params
     * @param string $pattern
     * @return bool
     */
    public function validateParamRegex(string $params, string $pattern = self::VALIDATION_RULE_PATTERN): bool
    {
        $validator = $this->regexValidatorFactory->create(['pattern' => $pattern]);

        if ($params && !$validator->isValid($params)) {
            return false;
        }

        return true;
    }
}
