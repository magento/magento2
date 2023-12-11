<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Ui\DataProvider\Product;

use Magento\Framework\Api\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * DataProvider for product reviews
 *
 * @api
 *
 * @method Collection getCollection
 * @since 100.1.0
 */
class ReviewDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     * @since 100.1.0
     */
    protected $collectionFactory;

    /**
     * @var RequestInterface
     * @since 100.1.0
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
     * @inheritdoc
     * @since 100.1.0
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
     * Returns prepared field name
     *
     * @param string $name
     * @return string
     */
    private function getPreparedField(string $name): string
    {
        $preparedName = '';

        if (in_array($name, ['review_id', 'created_at', 'status_id'])) {
            $preparedName = 'rt.' . $name;
        } elseif (in_array($name, ['title', 'nickname', 'detail'])) {
            $preparedName = 'rdt.' . $name;
        } elseif ($name === 'review_created_at') {
            $preparedName = 'rt.created_at';
        }

        return $preparedName ?: $name;
    }

    /**
     * @inheritDoc
     */
    public function addOrder($field, $direction)
    {
        $this->getCollection()->setOrder($this->getPreparedField($field), $direction);
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function addFilter(Filter $filter)
    {
        $field = $filter->getField();
        $filter->setField($this->getPreparedField($field));

        parent::addFilter($filter);
    }
}
