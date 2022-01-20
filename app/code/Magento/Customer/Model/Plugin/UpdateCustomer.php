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
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;

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
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param RestRequest $request
     * @param UserContextInterface|null $userContext
     */
    public function __construct(RestRequest $request, ?UserContextInterface $userContext = null)
    {
        $this->request = $request;
        $this->userContext = $userContext ?? ObjectManager::getInstance()->get(UserContextInterface::class);
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
        $customerSessionId = $this->userContext->getUserType() === $this->userContext::USER_TYPE_CUSTOMER ?
                             (int)$this->userContext->getUserId() : 0;
        $customerId = (int)$this->request->getParam('customerId');
        $bodyParams = $this->request->getBodyParams();
        if (!isset($bodyParams['customer']['Id']) && $customerId) {
            if ($customerId === $customerSessionId || $customerSessionId === 0) {
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
