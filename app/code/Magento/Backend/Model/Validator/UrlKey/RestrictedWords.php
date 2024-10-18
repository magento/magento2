<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Validator\UrlKey;

use Magento\Framework\Validator\UrlKey;

/**
 * Class RestrictedWords validates if urlKey doesn't matches restricted words(endpoint names)
 */
class RestrictedWords implements UrlKeyValidatorInterface
{
    /**
     * @var UrlKey
     */
    private $urlKey;

    /**
     * @param UrlKey $urlKey
     */
    public function __construct(
        UrlKey $urlKey
    ) {
        $this->urlKey = $urlKey;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $urlKey): array
    {
        $errors = [];
        if (!$this->urlKey->isValid($urlKey)) {
            $errors[] = __(
                'URL key "%1" matches a reserved endpoint name (%2). Use another URL key.',
                $urlKey,
                implode(', ', $this->urlKey->getRestrictedValues())
            );
        }

        return $errors;
    }
}
