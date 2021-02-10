<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Authentication data
 *
 * @api
 * @since 100.4.0
 */
interface AuthenticationDataInterface extends ExtensibleDataInterface
{
    /**
     * Get Customer Id
     *
     * @return int
     * @since 100.4.0
     */
    public function getCustomerId(): int;

    /**
     * Get Admin Id
     *
     * @return int
     * @since 100.4.0
     */
    public function getAdminId(): int;

    /**
     * Get extension attributes
     *
     * Fully qualified namespaces is needed for proper work of ccode generation
     *
     * @return \Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataExtensionInterface|null
     * @since 100.4.0
     */
    public function getExtensionAttributes(): ?AuthenticationDataExtensionInterface;
}
