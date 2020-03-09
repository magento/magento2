<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DataObject;

use Ramsey\Uuid\Uuid;

/**
 * Class IdentityValidator
 *
 * Class for validating Uuid's
 */
class IdentityValidator implements IdentityValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function isValid(string $value): bool
    {
        $isValid = Uuid::isValid($value);
        return $isValid;
    }
}
