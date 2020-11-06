<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Model\ResourceModel\Product\Compare\CompareList as CompareListResource;

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
     *
     * @return int
     */
    public function execute(string $maskedListId): int
    {
        $compareList = $this->compareListFactory->create();
        $this->compareListResource->load($compareList, $maskedListId, 'list_id_mask');

        return (int)$compareList->getListId();
    }
}
