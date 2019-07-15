<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Compare;

use Magento\Catalog\Model\CompareList\CustomerIdByHashedIdProviderInterface;
use Magento\Catalog\Model\CompareList\HashedListIdToListIdInterface;

class RemoveFromList
{
    /**
     * @var ItemFactory
     */
    private $compareItemFactory;

    /**
     * @var HashedListIdToListIdInterface
     */
    private $hashedListIdToListId;

    /**
     * @var CustomerIdByHashedIdProviderInterface
     */
    private $customerIdByHashedIdProvider;

    /**
     * @param ItemFactory $compareItemFactory
     * @param HashedListIdToListIdInterface $hashedListIdToListId
     * @param CustomerIdByHashedIdProviderInterface $customerIdByHashedIdProvider
     */
    public function __construct(
        ItemFactory $compareItemFactory,
        HashedListIdToListIdInterface $hashedListIdToListId,
        CustomerIdByHashedIdProviderInterface $customerIdByHashedIdProvider
    ) {
        $this->compareItemFactory = $compareItemFactory;
        $this->hashedListIdToListId = $hashedListIdToListId;
        $this->customerIdByHashedIdProvider = $customerIdByHashedIdProvider;
    }

    /**
     * @param int $customerId
     * @param string $hashedId
     * @param int $productId
     */
    public function execute(int $customerId, string $hashedId, int $productId)
    {
        $item = $this->compareItemFactory->create();
        if (0 !== $customerId && null !== $customerId) {
            $item->setCustomerId($customerId);
        }

        $listId = $this->hashedListIdToListId->execute($hashedId);
        $listCustomerId = $this->customerIdByHashedIdProvider->get($hashedId);

        $item->setCatalogCompareListId($listId);
        $item->loadByProduct($productId);
        if ($item->getId() && $customerId === $listCustomerId) {
            $item->delete();
        }
    }
}
