<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Validator\UrlKey;

/**
 * Class Composite validates if urlKey doesn't matches frontName or restricted words(endpoint names)
 */
class CompositeUrlKey implements UrlKeyValidatorInterface
{
    /**
     * @var UrlKeyValidatorInterface[]
     */
    private $validators;

    /**
     * @param array $validators
     */
    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $urlKey): array
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $errors[] = $validator->validate($urlKey);
        }

        return array_merge([], ...$errors);
    }
}
