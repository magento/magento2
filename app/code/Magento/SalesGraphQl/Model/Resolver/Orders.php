<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccountInterface;

/**
 * {@inheritdoc}
 */
class Orders implements ResolverInterface
{
	/**
	 * @var UserContextInterface
	 */
	private $userContext;

	/**
	 * @var CollectionFactoryInterface
	 */
	private $collectionFactory;

	/**
	 * @var CheckCustomerAccountInterface
	 */
	private $checkCustomerAccount;

	/**
	 * Orders constructor.
	 * @param UserContextInterface $userContext
	 * @param CollectionFactoryInterface $collectionFactory
	 */
	public function __construct(
		UserContextInterface $userContext,
		CollectionFactoryInterface $collectionFactory,
		CheckCustomerAccountInterface $checkCustomerAccount
	) {
		$this->userContext = $userContext;
		$this->collectionFactory = $collectionFactory;
		$this->checkCustomerAccount = $checkCustomerAccount;

	}

	/**
	 * {@inheritdoc}
	 */
	public function resolve(
		Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
	) {

		$customerId = $this->userContext->getUserId();

		$this->checkCustomerAccount->execute($customerId, $this->userContext->getUserType());

		$orders = $this->collectionFactory->create($customerId);
		$items = [];

		// @TODO Add shipping & billing address in response
		// @TODO Add order currency object in response
		/** @var \Magento\Sales\Model\Order $order */
		foreach ($orders as $order) {
			$items[] = [
				'id' => $order->getId(),
				'increment_id' => $order->getIncrementId(),
				'created_at' => $order->getCreatedAt(),
				'grant_total' => $order->getGrandTotal(),
				'state' => $order->getState(),
				'status' => $order->getStatus()
			];
		}

		return ['items' => $items];
	}
}