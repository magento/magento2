<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCustomerGraphQl\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @inheritdoc
 */
class TierPrices implements ResolverInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var int
     */
    private $customerGroupId = null;

    /**
     * @var array
     */
    private $productIds = [];

    /**
     * @param CollectionFactory $collectionFactory
     * @param ValueFactory $valueFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ValueFactory $valueFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->collection = $collectionFactory->create();
        $this->valueFactory = $valueFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (null === $this->customerGroupId) {
            $this->customerGroupId = $this->getCustomerGroupId($context);
        }

        /** @var Product $product */
        $product = $value['model'];
        $productId = $product->getId();
        $this->productIds[] = $productId;
        $that = $this;

        return $this->valueFactory->create(
            function () use ($that, $productId, $context) {
                $tierPrices = [];
                if (empty($that->productIds)) {
                    return [];
                }
                if (!$that->collection->isLoaded()) {
                    $that->collection->addIdFilter($that->productIds);
                    $that->collection->addTierPriceDataByGroupId($that->customerGroupId);
                }
                /** @var \Magento\Catalog\Model\Product $item */
                foreach ($that->collection as $item) {
                    if ($item->getId() === $productId) {
                        // Try to extract all requested fields from the loaded collection data
                        foreach ($item->getTierPrices() as $tierPrice) {
                            $tierPrices[] = $tierPrice->getData();
                        }
                    }
                }
                return $tierPrices;
            }
        );
    }

    /**
     * Get the customer group Id.
     *
     * @param \Magento\GraphQl\Model\Query\ContextInterface $context
     *
     * @return int
     */
    private function getCustomerGroupId(\Magento\GraphQl\Model\Query\ContextInterface $context)
    {
        $currentUserId = $context->getUserId();
        if (!$currentUserId) {
            $customerGroupId = GroupManagement::NOT_LOGGED_IN_ID;
        } else {
            try {
                $customer = $this->customerRepository->getById($currentUserId);
            } catch (NoSuchEntityException $e) {
                throw new GraphQlNoSuchEntityException(
                    __('Customer with id "%customer_id" does not exist.', ['customer_id' => $currentUserId]),
                    $e
                );
            }
            $customerGroupId = $customer->getGroupId();
        }
        return $customerGroupId;
    }
}
