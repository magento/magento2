<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface as BaseContextInterface;

/**
 * Resolver Context is used as a shared data extensible object in all resolvers that implement @see ResolverInterface.
 *
 * GraphQL will pass the same instance of this interface to each field resolver, so these resolvers could have
 * shared access to the same data for ease of implementation purposes.
 */
interface ContextInterface extends BaseContextInterface, ExtensibleDataInterface
{
    /**
     * Get the type of a user
     *
     * @return int|null
     */
    public function getUserType(): ?int;

    /**
     * Get id of the user
     *
     * @return int|null
     */
    public function getUserId(): ?int;

    /**
     * Get extension attributes
     *
     * @return \Magento\GraphQl\Model\Query\ContextExtensionInterface
     */
    public function getExtensionAttributes(): \Magento\GraphQl\Model\Query\ContextExtensionInterface;
}
