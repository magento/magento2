<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Attribute;

/**
 * @api
 * @since 2.0.0
 */
class Repository extends \Magento\Catalog\Ui\Component\Listing\Attribute\AbstractRepository
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request
    ) {
        parent::__construct($productAttributeRepository, $searchCriteriaBuilder);
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function buildSearchCriteria()
    {
        return $this->searchCriteriaBuilder
            ->addFilter('attribute_code', $this->request->getParam('attributes_codes', []), 'in')
            ->create();
    }
}
