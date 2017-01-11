<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Theme\Model\ResourceModel\Design\Config\Collection;
use Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Framework\App\RequestInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataProvider\DataLoader
     */
    protected $dataLoader;

    /**
     * @var DataProvider\MetadataLoader
     */
    private $metadataLoader;

    /**
     * @var SettingChecker
     */
    private $settingChecker;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DataProvider\DataLoader $dataLoader
     * @param DataProvider\MetadataLoader $metadataLoader
     * @param CollectionFactory $configCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataProvider\DataLoader $dataLoader,
        DataProvider\MetadataLoader $metadataLoader,
        CollectionFactory $configCollectionFactory,
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
        $this->dataLoader = $dataLoader;
        $this->metadataLoader = $metadataLoader;

        $this->collection = $configCollectionFactory->create();

        $this->meta = array_merge($this->meta, $this->metadataLoader->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = $this->dataLoader->getData();
        return $this->loadedData;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        if (!isset($meta['other_settings']['children'])) {
            return $meta;
        }

        $request = $this->getRequest()->getParams();
        if (!isset($request['scope'])) {
            return $meta;
        }

        $scope = $request['scope'];
        $scopeCode = $this->getScopeCodeResolver()->resolve(
            $scope,
            isset($request['scope_id']) ? $request['scope_id'] : null
        );

        foreach ($meta['other_settings']['children'] as $settingGroupName => &$settingGroup) {
            foreach ($settingGroup['children'] as $fieldName => &$field) {
                $path = sprintf(
                    'design/%s/%s',
                    $settingGroupName,
                    preg_replace('/^' . $settingGroupName . '_/', '', $fieldName)
                );
                $isReadOnly = $this->getSettingChecker()->isReadOnly(
                    $path,
                    $scope,
                    $scopeCode
                );

                if ($isReadOnly) {
                    $field['arguments']['data']['config']['disabled'] = true;
                    $field['arguments']['data']['config']['is_disable_inheritance'] = true;
                }
            }
        }

        return $meta;
    }

    /**
     * @deprecated
     * @return ScopeCodeResolver
     */
    private function getScopeCodeResolver()
    {
        if ($this->scopeCodeResolver === null) {
            $this->scopeCodeResolver = ObjectManager::getInstance()->get(ScopeCodeResolver::class);
        }
        return $this->scopeCodeResolver;
    }

    /**
     * @deprecated
     * @return SettingChecker
     */
    private function getSettingChecker()
    {
        if ($this->settingChecker === null) {
            $this->settingChecker = ObjectManager::getInstance()->get(SettingChecker::class);
        }
        return $this->settingChecker;
    }

    /**
     * @deprecated
     * @return RequestInterface
     */
    private function getRequest()
    {
        if ($this->request === null) {
            $this->request = ObjectManager::getInstance()->get(RequestInterface::class);
        }
        return $this->request;
    }
}
