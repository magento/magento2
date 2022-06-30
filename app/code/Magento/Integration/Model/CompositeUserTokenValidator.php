<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model;

use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\UserTokenValidatorInterface;

class CompositeUserTokenValidator implements UserTokenValidatorInterface
{
    /**
     * @var UserTokenValidatorInterface[]
     */
    private $validators;

    /**
     * @param UserTokenValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * @inheritDoc
     */
    public function validate(UserToken $token): void
    {
        foreach ($this->validators as $tokenValidator) {
            $tokenValidator->validate($token);
        }
    }
}
