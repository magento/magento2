<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Model\ResourceModel\Product\Compare\ListIdMask as ListIdMaskMaskResource;

/**
 *  CompareListId to MaskedListId resolver
 */
class CompareListIdToMaskedListId
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
     * @param int $listId
     *
     * @return null|string
     */
    public function execute(int $listId): ?string
    {
        $listIdMask = $this->listIdMaskFactory->create();
        $this->listIdMaskResource->load($listIdMask, $listId, 'list_id');
        return $listIdMask->getMaskedId() ?? null;
    }
}
