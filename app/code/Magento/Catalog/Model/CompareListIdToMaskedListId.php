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
 *  CompareListId to MaskedListId resolver
 */
class CompareListIdToMaskedListId
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
     * Get listIdMask by listId
     *
     * @param int $listId
     *
     * @param int|null $customerId
     * @return null|string
     * @throws LocalizedException
     */
    public function execute(int $listId, int $customerId = null): ?string
    {
        $compareList = $this->compareListFactory->create();
        $this->compareListResource->load($compareList, $listId, 'list_id');
        if ((int)$compareList->getCustomerId() !== (int)$customerId) {
            throw new LocalizedException(__('This customer is not authorized to access this list'));
        }
        return $compareList->getListIdMask() ?? null;
    }
}
