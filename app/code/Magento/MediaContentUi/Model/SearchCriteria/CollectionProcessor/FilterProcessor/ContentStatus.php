<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentUi\Model\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\MediaContentApi\Model\GetAssetIdByContentStatusInterface;

class ContentStatus implements CustomFilterInterface
{
    private const TABLE_ALIAS = 'main_table';

    /**
     * @var GetAssetIdByContentStatusInterface
     */
    private $getAssetIdByContentStatus;

    /**
     * ContentStatus constructor.
     * @param GetAssetIdByContentStatusInterface $getAssetIdByContentStatus
     */
    public function __construct(
        GetAssetIdByContentStatusInterface $getAssetIdByContentStatus
    ) {
        $this->getAssetIdByContentStatus = $getAssetIdByContentStatus;
    }

    /**
     * @inheritDoc
     */
    public function apply(Filter $filter, AbstractDb $collection): bool
    {
        $collection->addFieldToFilter(
            self::TABLE_ALIAS . '.id',
            ['in' => $this->getAssetIdByContentStatus->execute($filter->getValue())]
        );

        return true;
    }

}
