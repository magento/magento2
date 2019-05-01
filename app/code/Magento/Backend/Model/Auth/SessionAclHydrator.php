<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backend\Model\Auth;

use Magento\Backend\Spi\SessionAclHydratorInterface;
use Magento\Framework\Acl;

/**
 * @inheritDoc
 */
class SessionAclHydrator extends Acl implements SessionAclHydratorInterface
{
    /**
     * @inheritDoc
     */
    public function extract(Acl $acl): array
    {
        return ['rules' => $acl->_rules, 'resources' => $acl->_resources, 'roles' => $acl->_roleRegistry];
    }

    /**
     * @inheritDoc
     */
    public function hydrate(Acl $target, array $data): void
    {
        $target->_rules = $data['rules'];
        $target->_resources = $data['resources'];
        $target->_roleRegistry = $data['roles'];
    }
}
