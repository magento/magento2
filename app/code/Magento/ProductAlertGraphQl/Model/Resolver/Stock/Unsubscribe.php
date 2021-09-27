<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlertGraphQl\Model\Resolver\Stock;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\ProductAlert\Helper\Data as AlertsHelper;
use Magento\ProductAlert\Model\ResourceModel\Stock as StockResourceModel;
use Magento\ProductAlert\Model\StockFactory;

/**
 * Unsubscribe to product stock alert
 */
class Unsubscribe implements ResolverInterface
{
    /**
     * @var AlertsHelper
     */
    private $helper;

    /**
     * @var StockFactory
     */
    private $stockFactory;

    /**
     * @var StockResourceModel
     */
    private $stockResource;

    /**
     * @param AlertsHelper $helper
     * @param StockFactory $stockFactory
     * @param StockResourceModel $stockResource
     */
    public function __construct(
        AlertsHelper $helper,
        StockFactory $stockFactory,
        StockResourceModel $stockResource
    ) {
        $this->helper = $helper;
        $this->stockFactory = $stockFactory;
        $this->stockResource = $stockResource;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$this->helper->isStockAlertAllowed()) {
            throw new GraphQlInputException(__('The product stock alerts is currently disabled.'));
        }

        $customerId = $context->getUserId();
        $store = $context->getExtensionAttributes()->getStore();

        /* Guest checking */
        if (!$customerId) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on product alerts.'));
        }

        $productId = ((int) $args['productId']) ?: null;

        $model = $this->stockFactory->create()
                ->setCustomerId($customerId)
                ->setProductId($productId)
                ->setWebsiteId($store->getWebsiteId())
                ->setStoreId($store->getId())
                ->loadByParam();
        
        if (!$model->getId()) {
            throw new GraphQlNoSuchEntityException(__('The current user isn\'t subscribed to stocK alert.'));
        } 

        $this->stockResource->delete($model);

        return true;
    }
}
