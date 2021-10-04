<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlertGraphQl\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\ProductAlert\Helper\Data as AlertsHelper;
use Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory;

/**
 * Product price alerts for Customer
 */
class Price implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $priceCollectionFactory;

    /**
     * @var AlertsHelper
     */
    private $helper;

    /**
     * @param CollectionFactory $priceCollectionFactory
     * @param AlertsHelper $helper
     */
    public function __construct(
        CollectionFactory $priceCollectionFactory,
        AlertsHelper $helper
    ) {
        $this->priceCollectionFactory = $priceCollectionFactory;
        $this->helper = $helper;
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
        if (!$this->helper->isPriceAlertAllowed()) {
            throw new GraphQlInputException(__('The product price alerts is currently disabled.'));
        }

        $customerId = $context->getUserId();
        $store = $context->getExtensionAttributes()->getStore();

        /* Guest checking */
        if (!$customerId) {
            throw new GraphQlAuthorizationException(
                __('The current user cannot perform operations on product alerts.')
            );
        }

        $alerts = $this->getProductAlertsForCustomer($customerId, $store->getId());
        $data = [];
        foreach ($alerts as $alert) {
            $data[] = [
                'id' => $alert->getId(),
                'add_date' => $alert->getAddDate(),
                'model' => $alert,
            ];
        }

        return $data;
    }

    /**
     * Get price alerts for customer
     *
     * @param int $customerId
     * @param int $storeId
     * @return array
     */
    private function getProductAlertsForCustomer($customerId, $storeId): array
    {
        $priceCollection = $this->priceCollectionFactory->create();
        $connection = $priceCollection->getConnection();
        $priceCollection->addFilter('customer_id', $connection->quoteInto('customer_id=?', $customerId), 'string')
            ->addFilter('store_id', $connection->quoteInto('store_id=?', $storeId), 'string');

        return $priceCollection->getItems();
    }
}
