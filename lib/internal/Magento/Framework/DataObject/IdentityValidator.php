<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

use Ramsey\Uuid\Uuid;

/**
 * Class IdentityValidator
 */
class IdentityValidator implements IdentityValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $isValid = Uuid::isValid($value);
        return $isValid;
    }
}
