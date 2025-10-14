<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataExtensionInterface;

/**
 * @inheritdoc
 */
class AuthenticationData implements AuthenticationDataInterface
{
    /**
     * @var int
     */
    private $customerId;

    /**
     * @var int
     */
    private $adminId;

    /**
     * @var AuthenticationDataExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param int $customerId
     * @param int $adminId
     * @param AuthenticationDataExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        int $customerId,
        int $adminId,
        ?AuthenticationDataExtensionInterface $extensionAttributes = null
    ) {
        $this->customerId = $customerId;
        $this->adminId = $adminId;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * @inheritdoc
     */
    public function getAdminId(): int
    {
        return $this->adminId;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?AuthenticationDataExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
