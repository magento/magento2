<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Ui\DataProvider;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\ImportExport\Model\ExportFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class ExportFilterDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ExportFactory
     */
    protected $exportFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ExportFactory $exportFactory
     * @param RequestInterface $request
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ExportFactory $exportFactory,
        RequestInterface $request,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collectionFactory = $collectionFactory;
        $this->exportFactory = $exportFactory;
        $this->request = $request;
    }

    /**
     * Return collection
     *
     * @return AbstractCollection
     */
    public function getCollection() :AbstractCollection
    {
        if (!$this->collection) {
            $entity = $this->request->getParam('entity');
            if ($entity) {
                $this->collection = $this->exportFactory->create()
                    ->setData('entity', $entity)
                    ->getEntityAttributeCollection()
                    ->clear();
            } else {
                $this->collection = $this->collectionFactory->create()
                    ->setEntityTypeFilter(0);
            }
        }

        return $this->collection;
    }
}
