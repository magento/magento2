<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Api;

/**
 * Interface Extra abilities provider.
 */
interface ExtraAbilitiesProviderInterface
{
    /**
     * Gets extra abilities.
     *
     * @param int $customerId
     * @return array
     */
    public function getExtraAbilities(int $customerId): array;
}
