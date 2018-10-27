<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic;

<<<<<<< HEAD
=======
use Magento\Framework\App\Config\ScopeConfigInterface;
>>>>>>> upstream/2.2-develop
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;

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
     * @param EngineResolverInterface $engineResolver
     * @param string[] $dataProviders
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EngineResolverInterface $engineResolver,
        $dataProviders
    ) {
        $this->objectManager = $objectManager;
        $configValue = $engineResolver->getCurrentSearchEngine();
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
                'DataProvider not instance of interface ' . DataProviderInterface::class
            );
        }
        return $dataProvider;
    }
}
