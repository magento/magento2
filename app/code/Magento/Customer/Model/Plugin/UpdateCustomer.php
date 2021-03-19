<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Integration\Model\Oauth\Token;

/**
 * Update customer by id from request param
 */
class UpdateCustomer
{
    /**
     * @var RestRequest
     */
    private $request;

    /**
     * @var Token
     */
    private $token;

    /**
     * @param RestRequest $request
     * @param Token|null $token
     */
    public function __construct(
        RestRequest $request,
        Token $token = null
    ) {
        $this->request = $request;
        $this->token = $token ?: ObjectManager::getInstance()
            ->get(Token::class);
    }

    /**
     * Update customer by id from request if exist
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface $customer
     * @param string|null $passwordHash
     * @return array
     */
    public function beforeSave(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterface $customer,
        ?string $passwordHash = null
    ): array {
        $customerId = $this->request->getParam('customerId');
        $cookie = $this->request->getHeader('cookie');
        $headerToken = $this->request->getHeader('Authorization');
        if ($customerId && !str_contains($cookie, 'PHPSESSID')) {
            if (str_contains($headerToken, 'Bearer')) {
                $token = "Bearer " . $this->token->loadByCustomerId($customerId)->getData('token');
                if ($headerToken === $token) {
                    $customer = $this->getUpdatedCustomer($customerRepository->getById($customerId), $customer);
                }
            } else {
                $customer = $this->getUpdatedCustomer($customerRepository->getById($customerId), $customer);
            }
        }

        return [$customer, $passwordHash];
    }

    /**
     * Return updated customer
     *
     * @param CustomerInterface $originCustomer
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    private function getUpdatedCustomer(
        CustomerInterface $originCustomer,
        CustomerInterface $customer
    ): CustomerInterface {
        $newCustomer = clone $originCustomer;
        foreach ($customer->__toArray() as $name => $value) {
            if ($name === CustomerInterface::CUSTOM_ATTRIBUTES) {
                $value = $customer->getCustomAttributes();
            } elseif ($name === CustomerInterface::EXTENSION_ATTRIBUTES_KEY) {
                $value = $customer->getExtensionAttributes();
            } elseif ($name === CustomerInterface::KEY_ADDRESSES) {
                $value = $customer->getAddresses();
            }

            $newCustomer->setData($name, $value);
        }

        return $newCustomer;
    }
}
