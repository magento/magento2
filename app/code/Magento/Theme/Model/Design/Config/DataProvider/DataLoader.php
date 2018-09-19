<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config\DataProvider;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Theme\Api\DesignConfigRepositoryInterface;

class DataLoader
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var DesignConfigRepositoryInterface
     */
    protected $designConfigRepository;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param RequestInterface $request
     * @param DesignConfigRepositoryInterface $designConfigRepository
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        RequestInterface $request,
        DesignConfigRepositoryInterface $designConfigRepository,
        DataPersistorInterface $dataPersistor
    ) {
        $this->request = $request;
        $this->designConfigRepository = $designConfigRepository;
        $this->dataPersistor = $dataPersistor;
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
        $designConfig = $this->designConfigRepository->getByScope($scope, $scopeId);
        $fieldsData = $designConfig->getExtensionAttributes()->getDesignConfigData();
        $data = [];
        foreach ($fieldsData as $fieldData) {
            $data[$scope][$fieldData->getFieldConfig()['field']] = $fieldData->getValue();
        }

        $storedData = $this->dataPersistor->get('theme_design_config');
        if (isset($storedData['scope']) && isset($storedData['scope_id'])
            && $storedData['scope'] == $scope && $storedData['scope_id'] == $scopeId
        ) {
            $data[$scope] = $storedData;
            $this->dataPersistor->clear('theme_design_config');
        }

        return $data;
    }
}
