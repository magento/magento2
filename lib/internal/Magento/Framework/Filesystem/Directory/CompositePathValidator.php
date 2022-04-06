<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filesystem\Directory;

/**
 * Validates paths using driver.
 */
class CompositePathValidator implements PathValidatorInterface
{
    /**
     * @var PathValidatorInterface[]
     */
    private $validators;

    /**
     * @param PathValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * @inheritDoc
     */
    public function validate(
        string $directoryPath,
        string $path,
        ?string $scheme = null,
        bool $absolutePath = false
    ): void {
        foreach ($this->validators as $validator) {
            $validator->validate($directoryPath, $path, $scheme, $absolutePath);
        }
    }
}
