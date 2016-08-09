<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminNotification\Ui\Component\DataProvider;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection
     */
    protected $collection;
    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\AdminNotification\Model\ResourceModel\System\Message\CollectionFactory $messageCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\AdminNotification\Model\ResourceModel\System\Message\CollectionFactory $messageCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $messageCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
