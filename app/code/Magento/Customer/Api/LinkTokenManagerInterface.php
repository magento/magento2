<?php
/**
 * @package  Magento\Customer
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InputException;

/**
 * Interface LinkTokenManagerInterface
 */
interface LinkTokenManagerInterface
{
    /**
     * Change and store new reset password link token
     *
     * @param CustomerInterface $customerData
     * @param string $passwordLinkToken
     *
     * @throws InputException
     *
     * @return bool
     */
    public function changeToken(CustomerInterface $customerData, string $passwordLinkToken): bool;
}
