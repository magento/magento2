<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Resolver Context is used as a shared data extensible object in all resolvers that implement @see ResolverInterface.
 *
 * GraphQL will pass the same instance of this interface in each field resolver for, so these resolvers could have
 * shared access to the same data for ease of implementation purposes.
 */
interface ResolverContextInterface extends ExtensibleDataInterface
{
    /**
     * Get the type of a user
     *
     * @see \Magento\Authorization\Model\UserContextInterface for corespondent values
     *
     * @return int
     */
    public function getUserType();

    /**
     * Set type of a user
     *
     * @see \Magento\Authorization\Model\UserContextInterface for corespondent values
     *
     * @param int $typeId
     * @return $this
     */
    public function setUserType(int $typeId);

    /**
     * Get id of the user
     *
     * @return int
     */
    public function getUserId();

    /**
     * Set id of a user
     *
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\GraphQl\Model\ResolverContextExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\GraphQl\Model\ResolverContextExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\GraphQl\Model\ResolverContextExtensionInterface $extensionAttributes
    );
}
