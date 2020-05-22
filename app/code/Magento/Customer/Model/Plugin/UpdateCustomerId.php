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

/**
 * Update customer id from request param
 */
class UpdateCustomerId
{
    /**
     * @var RestRequest $request
     */
    private $request;

    /**
     * @param RestRequest $request
     */
    public function __construct(RestRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Update customer id from request if exist
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface $customer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CustomerRepositoryInterface $customerRepository, CustomerInterface $customer): void
    {
        $cartId = $this->request->getParam('customerId');

        if ($cartId) {
            $customer->setId($cartId);
        }
    }
}
