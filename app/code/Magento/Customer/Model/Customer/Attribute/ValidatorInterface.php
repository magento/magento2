<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Customer\Attribute;

use Magento\Framework\Api\AttributeInterface;

/**
 * Interface for customer custom attribute validator.
 */
interface ValidatorInterface
{
    /**
     * Validate customer attributes.
     *
     * Throws localized exception if is not valid.
     *
     * @param AttributeInterface $customAttribute
     * @return void
     * @throws \Throwable
     */
    public function validate(AttributeInterface $customAttribute): void;
}
