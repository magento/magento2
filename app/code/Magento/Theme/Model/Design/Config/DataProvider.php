<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ScopeFallbackResolverInterface;
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
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
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
     * @var ScopeFallbackResolverInterface
     */
    protected $scopeFallbackResolver;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param MetadataProvider $metadataProvider
     * @param CollectionFactory $configCollectionFactory
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param ScopeFallbackResolverInterface $scopeFallbackResolver
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        MetadataProvider $metadataProvider,
        CollectionFactory $configCollectionFactory,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        ScopeFallbackResolverInterface $scopeFallbackResolver,
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
        $this->scopeFallbackResolver = $scopeFallbackResolver;

        $this->scope = $this->request->getParam('scope');
        $this->scopeId = $this->request->getParam('scope_id');

        $this->prepareDefaultValues();
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
            $this->loadedData[$this->scope][$metadata[$item->getPath()]] = (string)$item->getValue()
                ?: $this->getFallbackValue($item->getPath());
        }

        $this->loadedData[$this->scope]['scope'] = $this->scope;
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
                $this->meta[$data['fieldset']]['fields'][$key]['default'] = $this->getFallbackValue($data['path']);
            }
        }
    }

    /**
     * Retrieve default value for parent scope
     *
     * @param string $path
     * @return string
     */
    protected function getFallbackValue($path)
    {
        list($scope, $scopeId) = $this->scopeFallbackResolver->getFallbackScope($this->scope, $this->scopeId);
        if ($scope) {
            return (string)$this->scopeConfig->getValue($path, $scope, $scopeId);
        }
        return '';
    }
}
