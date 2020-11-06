<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Model\ResourceModel\Product\Compare\CompareList as CompareListResource;

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
     * @return null|string
     */
    public function execute(int $listId): ?string
    {
        $compareList = $this->compareListFactory->create();
        $this->compareListResource->load($compareList, $listId, 'list_id');
        return $compareList->getListIdMask() ?? null;
    }
}
