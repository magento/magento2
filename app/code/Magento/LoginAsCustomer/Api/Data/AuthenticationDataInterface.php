<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Authentication data
 *
 * @api
 */
interface AuthenticationDataInterface extends ExtensibleDataInterface
{
    /**
     * Get Customer Id
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Get Admin Id
     *
     * @return int
     */
    public function getAdminId(): int;

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?AuthenticationDataExtensionInterface;
}
