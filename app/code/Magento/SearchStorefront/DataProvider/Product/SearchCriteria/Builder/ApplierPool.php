<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\DataProvider\Product\SearchCriteria\Builder;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\SearchStorefrontApi\Api\Data\ProductSearchRequestInterface;

/**
 * Class ApplierPool
 */
class ApplierPool implements ApplierInterface
{
    /**
     * @var array
     */
    private $searchCriteriaAppliers;

    /**
     * ApplierPool constructor.
     *
     * @param array $searchCriteriaAppliers
     */
    public function __construct(
        $searchCriteriaAppliers = []
    ) {
        $this->searchCriteriaAppliers = $searchCriteriaAppliers;
    }

    /**
     * Process search request with defined appliers.
     *
     * @param ProductSearchRequestInterface $request
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface
     * @throws LocalizedException
     */
    public function apply(
        ProductSearchRequestInterface $request,
        SearchCriteriaInterface $searchCriteria
    ) : SearchCriteriaInterface {
        foreach ($this->searchCriteriaAppliers as $keyName => $applier) {
            if (!$applier instanceof ApplierInterface) {
                throw new LocalizedException(__('Unsupported type of applier for %1', $keyName));
            }

            $searchCriteria = $applier->apply($request, $searchCriteria);
        }

        return $searchCriteria;
    }
}
