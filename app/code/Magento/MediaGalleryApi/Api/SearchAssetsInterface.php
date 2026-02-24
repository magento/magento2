<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Search media gallery assets by search criteria
 * @api
 */
interface SearchAssetsInterface
{
    /**
     * Search media gallery assets
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return AssetsSearchResultInterface[]
     * @throws LocalizedException
     */
    public function execute(SearchCriteriaInterface $searchCriteria): array;
}
