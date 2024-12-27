<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Address;

use Magento\Customer\Model\ResourceModel\Address\Grid\Collection as GridCollection;
use Magento\Customer\Model\ResourceModel\Address\Grid\CollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Custom DataProvider for customer addresses listing
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface $request,
     */
    private $request;

    /**
     * @var CountryFactory
     */
    private $countryDirectory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param CountryFactory $countryFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        CountryFactory $countryFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->countryDirectory = $countryFactory->create();
        $this->request = $request;
    }

    /**
     * Add country key for default billing/shipping blocks on customer addresses tab
     *
     * @return array
     */
    public function getData(): array
    {
        /** @var GridCollection $collection */
        $collection = $this->getCollection();
        $data['items'] = [];
        if ($this->request->getParam('parent_id')) {
            $collection->addFieldToFilter('parent_id', $this->request->getParam('parent_id'));
            $data = $collection->toArray();
        }
        foreach ($data['items'] as $key => $item) {
            if (isset($item['country_id']) && !isset($item['country'])) {
                $data['items'][$key]['country'] = $this->countryDirectory->loadByCode($item['country_id'])->getName();
            }
        }

        return $data;
    }

    /**
     * Add full text search filter to collection
     *
     * @param Filter $filter
     * @return void
     */
    public function addFilter(Filter $filter): void
    {
        /** @var GridCollection $collection */
        $collection = $this->getCollection();
        if ($filter->getField() === 'fulltext') {
            $value = $filter->getValue() !== null ? trim($filter->getValue()) : '';
            $collection->addFullTextFilter($value);
        } else {
            $collection->addFieldToFilter(
                $filter->getField(),
                [$filter->getConditionType() => $filter->getValue()]
            );
        }
    }
}
