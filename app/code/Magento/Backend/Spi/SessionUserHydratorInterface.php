<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backend\Spi;

use Magento\User\Model\User;

/**
 * Extract/hydrate user data to/from session.
 */
interface SessionUserHydratorInterface
{
    /**
     * Extract user data to store in session.
     *
     * @param User $user
     * @return array Array of scalars.
     */
    public function extract(User $user): array;

    /**
     * Fill User object with data from session.
     *
     * @param User $target
     * @param array $data Data from session.
     * @return void
     */
    public function hydrate(User $target, array $data): void;
}
