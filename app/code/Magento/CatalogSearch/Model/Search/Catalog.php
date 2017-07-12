<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search;

use Magento\Backend\Api\Search\ItemsInterface;
use Magento\Backend\Model\Search\ItemsAbstract;
use Magento\Search\Model\QueryFactory;

/**
 * Search model for backend search
 *
 * @deprecated
 */
class Catalog extends ItemsAbstract implements ItemsInterface
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
    protected $_adminhtmlData = null;

    /**
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param QueryFactory $queryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Framework\Stdlib\StringUtils $string,
        QueryFactory $queryFactory,
        array $data = []
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->string = $string;
        $this->queryFactory = $queryFactory;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $result = [];
        if (!$this->hasData(self::START) || !$this->hasData(self::LIMIT) || !$this->hasData(self::QUERY)) {
            return $result;
        }

        $collection = $this->queryFactory->get()
            ->getSearchCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('description')
            ->addBackendSearchFilter($this->getData(self::QUERY))
            ->setCurPage($this->getData(self::START))
            ->setPageSize($this->getData(self::LIMIT))
            ->load();

        foreach ($collection as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            $description = strip_tags($product->getDescription());
            $result[] = [
                'id' => 'product/1/' . $product->getId(),
                'type' => __('Product'),
                'name' => $product->getName(),
                'description' => $this->string->substr($description, 0, 30),
                'url' => $this->_adminhtmlData->getUrl('catalog/product/edit', ['id' => $product->getId()]),
            ];
        }
        return $result;
    }
}
