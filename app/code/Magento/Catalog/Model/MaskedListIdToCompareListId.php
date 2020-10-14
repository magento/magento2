<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Model\ResourceModel\Product\Compare\ListIdMask as ListIdMaskMaskResource;

/**
 * MaskedListId to ListId resolver
 */
class MaskedListIdToCompareListId
{
    /**
     * @var ListIdMaskFactory
     */
    private $listIdMaskFactory;

    /**
     * @var ListIdMaskMaskResource
     */
    private $listIdMaskResource;

    /**
     * @param ListIdMaskFactory $listIdMaskFactory
     * @param ListIdMaskMaskResource $listIdMaskResource
     */
    public function __construct(
        ListIdMaskFactory $listIdMaskFactory,
        ListIdMaskMaskResource $listIdMaskResource
    ) {
        $this->listIdMaskFactory = $listIdMaskFactory;
        $this->listIdMaskResource = $listIdMaskResource;
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
        $listIdMask = $this->listIdMaskFactory->create();
        $this->listIdMaskResource->load($listIdMask, $maskedListId, 'masked_id');

        return (int)$listIdMask->getListId();
    }
}
