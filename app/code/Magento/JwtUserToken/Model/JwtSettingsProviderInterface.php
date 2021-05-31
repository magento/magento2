<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Jwt\EncryptionSettingsInterface;

/**
 * Provides JWT settings to use for authentication.
 */
interface JwtSettingsProviderInterface
{
    /**
     * Prepare JWT settings to be used for tokens issued for a particular user context.
     *
     * @param UserContextInterface $userContext
     * @return EncryptionSettingsInterface
     */
    public function prepareSettingsFor(UserContextInterface $userContext): EncryptionSettingsInterface;

    /**
     * Prepare list of JWT settings for all types of accepted JWTs.
     *
     * @return EncryptionSettingsInterface[]
     */
    public function prepareAllAccepted(): array;
}
