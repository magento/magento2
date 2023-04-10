<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid as FrameworkUid;

/**
 * UID encode and decode for EAV attributes
 */
class Uid
{
    /**
     * @var FrameworkUid
     */
    private FrameworkUid $uid;

    /**
     * @param FrameworkUid $uid
     */
    public function __construct(FrameworkUid $uid)
    {
        $this->uid = $uid;
    }

    /**
     * Get EAV attribute UID based on entity type and attribute code
     *
     * @param string $entityType
     * @param string $attributeCode
     * @return string
     */
    public function encode(string $entityType, string $attributeCode): string
    {
        return $this->uid->encode(implode('/', [$entityType, $attributeCode]));
    }

    /**
     * Decode EAV attribute UID to an array containing entity type and attribute code
     *
     * @param string $uid
     * @return string[]
     * @throws GraphQlInputException
     */
    public function decode(string $uid): array
    {
        $entityTypeAndAttributeCode = explode('/', $this->uid->decode($uid));

        if (!is_array($entityTypeAndAttributeCode) || count($entityTypeAndAttributeCode) !== 2) {
            throw new GraphQlInputException(__('Value of uid "%1" is incorrect.', $uid));
        }

        return $entityTypeAndAttributeCode;
    }
}
