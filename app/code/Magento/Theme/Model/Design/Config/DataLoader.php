<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Theme\Model\ResourceModel\Design\Config\Collection;

class DataLoader
{
    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var ValueProcessor
     */
    protected $valueProcessor;

    /**
     * @param MetadataProvider $metadataProvider
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param ValueProcessor $valueProcessor
     */
    public function __construct(
        MetadataProvider $metadataProvider,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        ValueProcessor $valueProcessor
    ) {
        $this->metadataProvider = $metadataProvider;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->valueProcessor = $valueProcessor;
    }

    /**
     * Set data collection
     *
     * @param Collection $collection
     * @return $this
     */
    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * Retrieve configuration data
     *
     * @return array
     */
    public function getData()
    {
        $scope = $this->request->getParam('scope');
        $scopeId = $this->request->getParam('scope_id');

        $data = $this->loadData($scope, $scopeId);

        $data[$scope]['scope'] = $scope;
        $data[$scope]['scope_id'] = $scopeId;

        return $data;
    }

    /**
     * Load data
     *
     * @param string $scope
     * @param string $scopeId
     * @return array
     */
    protected function loadData($scope, $scopeId)
    {
        $data = [];

        $metadata = array_flip($this->getMetadata());

        $this->collection->addPathsFilter($this->getMetadata());
        $this->collection->addScopeIdFilter($scopeId);

        $items = $this->collection->getItems();
        foreach ($items as $item) {
            /** @var \Magento\Framework\App\Config\Value $item */
            $data[$scope][$metadata[$item->getPath()]] = $item->getValue();
        }

        $metadataKeys = array_diff_key($this->getMetadata(), (isset($data[$scope]) ? $data[$scope] : []));
        foreach ($metadataKeys as $path) {
            $data[$scope][$metadata[$path]] = $this->valueProcessor->process(
                $this->scopeConfig->getValue($path, $scope, $scopeId),
                $path
            );
        }

        return $data;
    }

    /**
     * Retrieve metadata
     *
     * @return array
     */
    protected function getMetadata()
    {
        if (!$this->metadata) {
            $this->metadata = $this->metadataProvider->get();
            array_walk($this->metadata, function (&$value) {
                $value = $value['path'];
            });
        }
        return $this->metadata;
    }
}
