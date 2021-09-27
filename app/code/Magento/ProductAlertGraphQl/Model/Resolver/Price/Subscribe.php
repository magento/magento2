<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlertGraphQl\Model\Resolver\Price;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\ProductAlert\Helper\Data as AlertsHelper;
use Magento\ProductAlert\Model\PriceFactory;

/**
 * Subscribe to product price alert
 */
class Subscribe implements ResolverInterface
{
    /**
     * @var AlertsHelper
     */
    private $helper;

    /**
     * @var PriceFactory
     */
    private $priceFactory;

    /**
     * @param AlertsHelper $helper
     * @param PriceFactory $priceFactory
     */
    public function __construct(
        AlertsHelper $helper,
        PriceFactory $priceFactory
    ) {
        $this->helper = $helper;
        $this->priceFactory = $priceFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$this->helper->isPriceAlertAllowed()) {
            throw new GraphQlInputException(__('The product price alerts is currently disabled.'));
        }

        $customerId = $context->getUserId();
        $store = $context->getExtensionAttributes()->getStore();

        /* Guest checking */
        if (!$customerId) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on product alerts.'));
        }

        $productId = ((int) $args['productId']) ?: null;

        $model = $this->priceFactory->create()
                ->setCustomerId($customerId)
                ->setProductId($productId)
                ->setWebsiteId($store->getWebsiteId())
                ->setStoreId($store->getId())
                ->loadByParam();

        if ($model->getId()) {
            throw new GraphQlAlreadyExistsException(__('The current user is currently subscribed to price alert.'));
        }

        $model->getResource()->save($model);

        return [
            'id' => $model->getId(),
            'add_date' => $model->getAddDate(),
            'model' => $model
        ];
    }
}
