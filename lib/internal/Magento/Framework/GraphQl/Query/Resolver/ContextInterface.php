<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Resolver Context is used as a shared data extensible object in all resolvers that implement @see ResolverInterface.
 *
 * GraphQL will pass the same instance of this interface to each field resolver, so these resolvers could have
 * shared access to the same data for ease of implementation purposes.
 */
interface ContextInterface extends ExtensibleDataInterface
{
    /**
     * Get the type of a user
     *
     * @see \Magento\Authorization\Model\UserContextInterface for corespondent values
     *
     * @return int
     */
    public function getUserType() : int;

    /**
     * Set type of a user
     *
     * @see \Magento\Authorization\Model\UserContextInterface for corespondent values
     *
     * @param int $typeId
     * @return ContextInterface
     */
    public function setUserType(int $typeId) : ContextInterface;

    /**
     * Get id of the user
     *
     * @return int
     */
    public function getUserId() : int;

    /**
     * Set id of a user
     *
     * @param int $userId
     * @return ContextInterface
     */
    public function setUserId(int $userId) : ContextInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Framework\GraphQl\Query\Resolver\ContextExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextExtensionInterface $extensionAttributes
     * @return ContextInterface
     */
    public function setExtensionAttributes(
        \Magento\Framework\GraphQl\Query\Resolver\ContextExtensionInterface $extensionAttributes
    ) : ContextInterface;
}
