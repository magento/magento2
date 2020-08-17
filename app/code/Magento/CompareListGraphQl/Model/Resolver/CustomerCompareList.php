<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\CompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ResourceModel\CompareList as ResourceCompareList;
use Magento\CompareListGraphQl\Model\Service\CompareListService;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;

class CustomerCompareList implements ResolverInterface
{
    /**
     * @var ResourceCompareList
     */
    private $resourceCompareList;

    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var CompareListService
     */
    private $compareListService;

    /**
     * @param ResourceCompareList           $resourceCompareList
     * @param CompareListFactory            $compareListFactory
     * @param CompareListService            $compareListService
     */
    public function __construct(
        ResourceCompareList $resourceCompareList,
        CompareListFactory $compareListFactory,
        CompareListService $compareListService
    ) {
        $this->resourceCompareList = $resourceCompareList;
        $this->compareListFactory = $compareListFactory;
        $this->compareListService = $compareListService;
    }

    /**
     * Get customer compare list
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return Value|mixed|void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $listId = (int)$this->getListIdByCustomerId($context->getUserId());

        if (!$listId) {
            return null;
        }

        return [
            'list_id' => $listId,
            'items' => $this->compareListService->getComparableItems($listId, $context, $store),
            'attributes' => $this->compareListService->getComparableAttributes($listId, $context)
        ];
    }

    /**
     * Get listId by Customer ID
     *
     * @param $customerId
     *
     * @return int|null
     */
    private function getListIdByCustomerId($customerId)
    {
        if ($customerId) {
            /** @var CompareList $compareListModel */
            $compareListModel = $this->compareListFactory->create();
            $this->resourceCompareList->load($compareListModel, $customerId, 'customer_id');
            return (int)$compareListModel->getId();
        }

        return null;
    }
}
