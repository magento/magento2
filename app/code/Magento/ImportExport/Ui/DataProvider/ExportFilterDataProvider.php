<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Ui\DataProvider;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as EavCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as EavCollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class ExportFilterDataProvider extends AbstractDataProvider
{
    /**
     * @var EavCollection
     */
    protected $collection;

    /**
     * @param EavCollectionFactory $eavCollectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        EavCollectionFactory $eavCollectionFactory,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $eavCollectionFactory->create();
    }
}
