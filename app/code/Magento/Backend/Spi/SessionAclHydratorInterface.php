<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backend\Spi;

use Magento\Framework\Acl;

/**
 * Extract/hydrate user's ACL data to/from session.
 */
interface SessionAclHydratorInterface
{
    /**
     * Extract ACL data to store in session.
     *
     * @param Acl $acl
     * @return array Array of scalars.
     */
    public function extract(Acl $acl): array;

    /**
     * Fill ACL object with data from session.
     *
     * @param Acl $target
     * @param array $data Data from session.
     * @return void
     */
    public function hydrate(Acl $target, array $data): void;
}
