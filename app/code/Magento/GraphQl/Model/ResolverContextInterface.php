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
     * Get type of a customer
     *
     * @return int
     */
    public function getCustomerType();

    /**
     * Set type of a customer
     *
     * @param int $typeId
     * @return $this
     */
    public function setCustomerType(int $typeId);

    /**
     * Get id of a customer
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set id of a customer
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\GraphQl\Model\ContextInterfaceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\GraphQl\Model\ContextInterfaceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\GraphQl\Model\ContextInterfaceExtensionInterface $extensionAttributes
    );
}
