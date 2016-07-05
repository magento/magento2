<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk;

use Magento\Framework\Bulk\Api\IdentityGeneratorInterface;

/**
 * Class IdentityService
 */
class IdentityService implements IdentityGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generateId()
    {
        return Uuid::uuid4();
    }

    /**
     * @inheritDoc
     */
    public function generateIdForData($data)
    {
        return Uuid::uuid3(Uuid::NAMESPACE_DNS, $data);
    }
}
