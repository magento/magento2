<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Ui\DataProvider;

use Magento\Store\Model\ResourceModel\Website\Grid\CollectionFactory as WebsiteCollectionFactory;

/**
 * Class WebsiteDataProvider
 */
class WebsiteDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * WebsiteDataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param WebsiteCollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        WebsiteCollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
    }
}
