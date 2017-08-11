<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search;

use Magento\Backend\Api\Search\ItemsInterface;
use Magento\Backend\Model\Search\SearchCriteria;
use Magento\Search\Model\QueryFactory;

/**
 * Search model for backend search
 *
 * @deprecated
 */
class Catalog implements ItemsInterface
{
    /**
     * Catalog search data
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory = null;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminHtmlData = null;

    /**
     * @param \Magento\Backend\Helper\Data $adminHtmlData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminHtmlData,
        \Magento\Framework\Stdlib\StringUtils $string,
        QueryFactory $queryFactory
    ) {
        $this->_adminHtmlData = $adminHtmlData;
        $this->string = $string;
        $this->queryFactory = $queryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(SearchCriteria $searchCriteria)
    {
        $result = [];
        if (!$searchCriteria->getStart() || !$searchCriteria->getLimit() || !$searchCriteria->getQuery()) {
            return $result;
        }

        $collection = $this->queryFactory->get()
            ->getSearchCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('description')
            ->addBackendSearchFilter($searchCriteria->getQuery())
            ->setCurPage($searchCriteria->getStart())
            ->setPageSize($searchCriteria->getLimit())
            ->load();

        foreach ($collection as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            $description = strip_tags($product->getDescription());
            $result[] = [
                'id' => 'product/1/' . $product->getId(),
                'type' => __('Product'),
                'name' => $product->getName(),
                'description' => $this->string->substr($description, 0, 30),
                'url' => $this->_adminHtmlData->getUrl('catalog/product/edit', ['id' => $product->getId()]),
            ];
        }
        return $result;
    }
}
