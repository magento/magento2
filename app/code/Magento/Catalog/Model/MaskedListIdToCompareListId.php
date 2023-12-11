<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Model\ResourceModel\Product\Compare\CompareList as CompareListResource;
use Magento\Framework\Exception\LocalizedException;

/**
 * MaskedListId to ListId resolver
 */
class MaskedListIdToCompareListId
{
    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var CompareListResource
     */
    private $compareListResource;

    /**
     * @param CompareListFactory $compareListFactory
     * @param CompareListResource $compareListResource
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        CompareListResource $compareListResource
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->compareListResource = $compareListResource;
    }

    /**
     * Get maskedId by listId
     *
     * @param string $maskedListId
     * @param int $customerId
     * @return int
     * @throws LocalizedException
     */
    public function execute(string $maskedListId, int $customerId = null): int
    {
        $compareList = $this->compareListFactory->create();
        $this->compareListResource->load($compareList, $maskedListId, 'list_id_mask');
        if ((int)$compareList->getCustomerId() !== (int)$customerId) {
            throw new LocalizedException(__('This customer is not authorized to access this list'));
        }
        return (int)$compareList->getListId();
    }
}
