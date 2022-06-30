<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Validator\UrlKey;

use Magento\Backend\App\Area\FrontNameResolver;

/**
 * Class FrontName validates if urlKey doesn't matches frontName
 */
class FrontName implements UrlKeyValidatorInterface
{
    /**
     * @var FrontNameResolver
     */
    private $frontNameResolver;

    /**
     * @param FrontNameResolver $frontNameResolver
     */
    public function __construct(
        FrontNameResolver $frontNameResolver
    ) {
        $this->frontNameResolver = $frontNameResolver;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $urlKey): array
    {
        $errors = [];
        $frontName = $this->frontNameResolver->getFrontName();
        if ($urlKey == $frontName) {
            $errors[] = __(
                'URL key "%1" matches a reserved endpoint name (%2). Use another URL key.',
                $urlKey,
                $frontName
            );
        }

        return $errors;
    }
}
