<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Address;

use Magento\Customer\Model\ResourceModel\Address\Grid\CollectionFactory;

/**
 * Custom DataProvider for customer addresses listing
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Add country key for default billing/shipping blocks on customer addresses tab
     *
     * @return array
     */
    public function getData(): array
    {
        $collection = $this->getCollection();
        $data = $collection->toArray();
        foreach ($data['items'] as $key => $item) {
            if (isset($item['country_id']) && !isset($item['country'])) {
                $data['items'][$key]['country'] = $item['country_id'];
            }
        }

        return $data;
    }
}
