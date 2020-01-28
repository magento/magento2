<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Attribute;

/**
 * Class AbstractRepository
 *
 * @api
 * @since 100.0.2
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var null|\Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    protected $attributes;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    abstract protected function buildSearchCriteria();

    /**
     * Get List of Attributes
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    public function getList()
    {
        if (null == $this->attributes) {
            $this->attributes = $this->productAttributeRepository
                ->getList($this->buildSearchCriteria())
                ->getItems();
        }
        return $this->attributes;
    }
}
