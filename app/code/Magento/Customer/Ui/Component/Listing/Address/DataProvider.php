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
     * @var \Magento\Framework\App\RequestInterface $request,
     */
    private $request;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryDirectory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Directory\Model\CountryFactory $countryFactory,
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Directory\Model\CountryFactory $countryFactory,
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
}
