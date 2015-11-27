<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Theme\Model\ResourceModel\Design\Config\Collection;
use Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var int
     */
    protected $scopeId;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param MetadataProvider $metadataProvider
     * @param CollectionFactory $configCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        MetadataProvider $metadataProvider,
        CollectionFactory $configCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->metadataProvider = $metadataProvider;
        $this->collection = $configCollectionFactory->create();
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->scope = $this->request->getParam('scope');
        $this->scopeId = $this->request->getParam('scope_id');

        $this->prepareDefaultValues($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $metadata = $this->metadataProvider->get();
        array_walk($metadata, function (&$value) {
            $value = $value['path'];
        });

        $this->collection->addPathsFilter($metadata);
        $this->collection->addScopeIdFilter($this->scopeId);

        $metadata = array_flip($metadata);

        $items = $this->collection->getItems();
        foreach ($items as $item) {
            /** @var \Magento\Framework\App\Config\Value $item */
            $this->loadedData[$this->scope][$metadata[$item->getPath()]] = $item->getValue();
        }

        $this->loadedData[$this->scope]['scope_id'] = $this->scopeId;

        return $this->loadedData;
    }

    /**
     * Prepare default values
     *
     * @return void
     */
    protected function prepareDefaultValues()
    {
        $metadata = $this->metadataProvider->get();
        if ($this->scope) {
            foreach ($metadata as $key => $data) {
                $defaultValue = $this->scopeConfig->getValue(
                    $data['path'],
                    $this->scope,
                    $this->request->getParam('scope_id')
                );
                $this->meta[$data['fieldset']]['fields'][$key]['default'] = $defaultValue;
            }
        }
    }
}
