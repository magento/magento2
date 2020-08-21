<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\CompareList as ModelCompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\Product\Compare\CompareList;
use Magento\Catalog\Model\ResourceModel\CompareList as ResourceCompareList;
use Magento\CompareListGraphQl\Model\Service\AddToCompareListService;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use Magento\CompareListGraphQl\Model\Service\CompareListService;

/**
 * Class add products to compare list
 */
class AddItemsToCompareList implements ResolverInterface
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
     * @var CompareList
     */
    private $compareList;

    /**
     * @var AddToCompareListService
     */
    private $addToCompareListService;

    /**
     * @var CompareListService
     */
    private $compareListService;

    /**
     * @param CompareListFactory $compareListFactory
     * @param ResourceCompareList $resourceCompareList
     * @param CompareList $compareList
     * @param AddToCompareListService $addToCompareListService
     * @param CompareListService $compareListService
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        ResourceCompareList $resourceCompareList,
        CompareList $compareList,
        AddToCompareListService $addToCompareListService,
        CompareListService $compareListService
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->resourceCompareList = $resourceCompareList;
        $this->compareList = $compareList;
        $this->addToCompareListService = $addToCompareListService;
        $this->compareListService = $compareListService;
    }

    /**
     * Add items to compare list
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return Value|mixed|void
     *
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $listId = (int)$args['id'];
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        /** @var  $compareListModel ModelCompareList*/
        $compareListModel = $this->compareListFactory->create();
        $this->resourceCompareList->load($compareListModel, $args['id']);

        if (!$compareListModel->getId()) {
            throw new GraphQlInputException(__('Can\'t load compare list.'));
        }

        $this->addToCompareListService->addToCompareList($listId, $args);

        return $this->compareListService->getCompareList($listId, $context, $store);
    }
}
