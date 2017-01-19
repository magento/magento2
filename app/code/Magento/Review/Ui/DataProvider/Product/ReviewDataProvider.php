<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Ui\DataProvider\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Magento\Review\Model\Review;

/**
 * Class ReviewDataProvider
 *
 * @method Collection getCollection
 */
class ReviewDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->getCollection()->addEntityFilter($this->request->getParam('current_product_id', 0))
            ->addStoreData();

        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        foreach ($this->getCollection() as $item) {
            $arrItems['items'][] = $item->toArray([]);
        }

        return $arrItems;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $field = $filter->getField();

        if (in_array($field, ['review_id', 'created_at', 'status_id'])) {
            $filter->setField('rt.' . $field);
        }

        if (in_array($field, ['title', 'nickname', 'detail'])) {
            $filter->setField('rdt.' . $field);
        }

        if ($field === 'review_created_at') {
            $filter->setField('rt.created_at');
        }

        parent::addFilter($filter);
    }
}
