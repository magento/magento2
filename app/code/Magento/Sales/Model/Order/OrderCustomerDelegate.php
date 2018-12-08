<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Customer\Api\AccountDelegationInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Api\OrderCustomerDelegateInterface;
use Magento\Sales\Observer\AssignOrderToCustomerObserver;

/**
<<<<<<< HEAD
 * @inheritDoc
=======
 * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 *
 * @see AssignOrderToCustomerObserver
 */
class OrderCustomerDelegate implements OrderCustomerDelegateInterface
{
    /**
     * @var OrderCustomerExtractor
     */
    private $customerExtractor;

    /**
     * @var AccountDelegationInterface
     */
    private $delegateService;

    /**
     * @param OrderCustomerExtractor $customerExtractor
     * @param AccountDelegationInterface $delegateService
     */
    public function __construct(
        OrderCustomerExtractor $customerExtractor,
        AccountDelegationInterface $delegateService
    ) {
        $this->customerExtractor = $customerExtractor;
        $this->delegateService = $delegateService;
    }

    /**
<<<<<<< HEAD
     * @inheritDoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function delegateNew(int $orderId): Redirect
    {
        return $this->delegateService->createRedirectForNew(
            $this->customerExtractor->extract($orderId),
            ['__sales_assign_order_id' => $orderId]
        );
    }
}
