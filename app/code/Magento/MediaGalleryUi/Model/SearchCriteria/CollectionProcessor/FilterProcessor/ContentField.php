<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\MediaContentApi\Api\GetAssetIdsByContentFieldInterface;

/**
 * Class responsible to filter a content field
 */
class ContentField implements CustomFilterInterface
{
    /**
     * @var GetAssetIdsByContentFieldInterface
     */
    private $getAssetIdsByContentStatus;

    /**
     * ContentField constructor.
     *
     * @param GetAssetIdsByContentFieldInterface $getAssetIdsByContentStatus
     */
    public function __construct(
        GetAssetIdsByContentFieldInterface $getAssetIdsByContentStatus
    ) {
        $this->getAssetIdsByContentStatus = $getAssetIdsByContentStatus;
    }

    /**
     * @inheritDoc
     */
    public function apply(Filter $filter, AbstractDb $collection): bool
    {
        $collection->addFieldToFilter(
            'main_table.id',
            ['in' => $this->getAssetIdsByContentStatus->execute($filter->getField(), $filter->getValue())]
        );

        return true;
    }
}
