<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service\Customer;

use Magento\Catalog\Model\CompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ResourceModel\Product\Compare\CompareList as ResourceCompareList;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory as CompareItemsCollectionFactory;
use Magento\CompareListGraphQl\Model\Service\AddToCompareList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Merge guest compare list into customer compare list
 */
class MergeCompareLists
{
    /**
     * @var CompareItemsCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var AddToCompareList
     */
    private $addProductToCompareList;

    /**
     * @var ResourceCompareList
     */
    private $resourceCompareList;

    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @param CompareItemsCollectionFactory $itemCollectionFactory
     * @param AddToCompareList $addProductToCompareList
     * @param ResourceCompareList $resourceCompareList
     * @param CompareListFactory $compareListFactory
     */
    public function __construct(
        CompareItemsCollectionFactory $itemCollectionFactory,
        AddToCompareList $addProductToCompareList,
        ResourceCompareList $resourceCompareList,
        CompareListFactory $compareListFactory
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->addProductToCompareList = $addProductToCompareList;
        $this->resourceCompareList = $resourceCompareList;
        $this->compareListFactory = $compareListFactory;
    }

    /**
     * Merge guest compare list into customer compare list
     *
     * @param int $guestListId
     * @param int $customerListId
     * @param ContextInterface $context
     * @return CompareList
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute(int $guestListId, int $customerListId, ContextInterface $context): CompareList
    {
        if ($guestListId === $customerListId) {
            throw new LocalizedException(__('Cannot merge a list with itself.'));
        }

        $connection = $this->resourceCompareList->getConnection();
        $connection->beginTransaction();

        try {
            $items = $this->itemCollectionFactory->create();
            $products = $items->getProductsByListId($guestListId);

            $this->addProductToCompareList->execute($customerListId, $products, $context);

            $guestList = $this->compareListFactory->create();
            $this->resourceCompareList->load($guestList, $guestListId, 'list_id');
            $this->resourceCompareList->delete($guestList);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $customerList = $this->compareListFactory->create();
        $this->resourceCompareList->load($customerList, $customerListId, 'list_id');

        return $customerList;
    }
}
