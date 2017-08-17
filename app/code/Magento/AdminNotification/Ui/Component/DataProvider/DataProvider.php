<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminNotification\Ui\Component\DataProvider;

use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\SynchronizedFactory;

/**
 * @api
 * @since 100.2.0
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param SynchronizedFactory $messageCollectionFactory
     * @param array $meta
     * @param array $data
     * @since 100.2.0
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        SynchronizedFactory $messageCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $messageCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
