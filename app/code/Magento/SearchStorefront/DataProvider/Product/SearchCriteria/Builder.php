<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\DataProvider\Product\SearchCriteria;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\SearchStorefrontApi\Api\Data\ProductSearchRequestInterface;

class Builder implements SearchCriteriaBuilderInterface
{
    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory
     */
    private $searchCriteriaFactory;

    /**
     * @var Builder\ApplierPool
     */
    private $applierPool;

    /**
     * @param \Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param Builder\ApplierPool $applierPool
     */
    public function __construct(
        \Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        Builder\ApplierPool $applierPool
    ) {
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->applierPool = $applierPool;
    }

    /**
     * Build search criteria from search service request.
     *
     * @param ProductSearchRequestInterface $request
     * @return SearchCriteriaInterface
     */
    public function build(ProductSearchRequestInterface $request) : SearchCriteriaInterface
    {
        $searchCriteria = $this->searchCriteriaFactory->create();
        $searchCriteria = $this->applierPool->apply($request, $searchCriteria);
        return $searchCriteria;
    }
}
