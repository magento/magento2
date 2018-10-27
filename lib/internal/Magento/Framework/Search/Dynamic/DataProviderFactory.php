<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * @api
 */
class DataProviderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $dataProvider;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param string $configPath
     * @param string[] $dataProviders
     * @param string $scope
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        $configPath,
        $dataProviders,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ) {
        $this->objectManager = $objectManager;
        $configValue = $scopeConfig->getValue($configPath, $scope);
        if (isset($dataProviders[$configValue])) {
            $this->dataProvider = $dataProviders[$configValue];
        } else {
            throw new \LogicException("DataProvider not found by config {$configValue}");
        }
    }

    /**
     * Create data provider
     *
     * @param array $data
     * @return DataProviderInterface
     */
    public function create(array $data = [])
    {
        $dataProvider = $this->objectManager->create($this->dataProvider, $data);
        if (!$dataProvider instanceof DataProviderInterface) {
            throw new \LogicException(
                'DataProvider not instance of interface \Magento\Framework\Search\Dynamic\DataProviderInterface'
            );
        }
        return $dataProvider;
    }
}
