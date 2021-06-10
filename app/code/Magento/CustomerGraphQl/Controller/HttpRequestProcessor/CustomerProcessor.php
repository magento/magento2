<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Controller\HttpRequestProcessor;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Context;
use Magento\Customer\Model\GroupManagement;
use Magento\GraphQl\Controller\HttpHeaderProcessorInterface;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQl\Model\Query\ContextInterface as GraphQlContext;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Process the "Authorization" header
 */
class CustomerProcessor implements HttpHeaderProcessorInterface
{
    /**
     * @var GraphQlContext
     */
    protected $graphQlContext;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Customer constructor.
     * @param ContextFactoryInterface $contextFactoryInterface
     * @param HttpContext $httpContext
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        ContextFactoryInterface $contextFactoryInterface,
        HttpContext $httpContext,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->graphQlContext = $contextFactoryInterface->create();
        $this->httpContext = $httpContext;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Handle the value of the customer and set the customer group id
     *
     * @param string $headerValue
     * @return void
     */
    public function processHeaderValue(string $headerValue) : void
    {
        $customerGroupId = GroupInterface::NOT_LOGGED_IN_ID;
        $customerIsLoggedIn = false;

        if ($headerValue) {
            if ($this->graphQlContext->getExtensionAttributes()->getIsCustomer() === true) {
                $customerId = $this->graphQlContext->getUserId();
                $customerGroupId = (int)$this->customerRepository->getById($customerId)->getGroupId();
            }

            if ($this->graphQlContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
                $customerIsLoggedIn = true;
            }
        }

        $this->httpContext->setValue(
            Context::CONTEXT_GROUP,
            $customerGroupId,
            GroupManagement::NOT_LOGGED_IN_ID
        );

        $this->httpContext->setValue(
            Context::CONTEXT_AUTH,
            $customerIsLoggedIn,
            false
        );
    }
}
