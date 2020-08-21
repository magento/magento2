<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\ResourceModel\CompareList as ResourceCompareList;
use Magento\CompareListGraphQl\Model\Service\CompareListService;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\CompareList as ModelCompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Get compare list
 */
class CompareList implements ResolverInterface
{
    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var ResourceCompareList
     */
    private $resourceCompareList;

    /**
     * @var CompareListService
     */
    private $compareListService;

    /**
     * @param CompareListFactory $compareListFactory
     * @param ResourceCompareList $resourceCompareList
     * @param CompareListService $compareListService
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        ResourceCompareList $resourceCompareList,
        CompareListService $compareListService
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->resourceCompareList = $resourceCompareList;
        $this->compareListService = $compareListService;
    }

    /**
     * Get compare list
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array|Value|mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
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
        /** @var  $compareListModel ModelCompareList*/
        $compareListModel = $this->compareListFactory->create();
        $this->resourceCompareList->load($compareListModel, $args['id']);
        $listId = (int)$compareListModel->getId();

        if (!$listId) {
            return null;
        }

        return $this->compareListService->getCompareList($listId, $context, $store);
    }
}
