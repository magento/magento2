<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Theme\Model\Design\BackendModelFactory;

/**
 * Class \Magento\Theme\Model\Design\Config\ValueProcessor
 *
 * @since 2.1.0
 */
class ValueProcessor
{
    /**
     * @var BackendModelFactory
     * @since 2.1.0
     */
    protected $backendModelFactory;

    /**
     * @param BackendModelFactory $backendModelFactory
     * @since 2.1.0
     */
    public function __construct(
        BackendModelFactory $backendModelFactory
    ) {
        $this->backendModelFactory = $backendModelFactory;
    }

    /**
     * Process value
     *
     * @param string $value
     * @param string $scope
     * @param string $scopeId
     * @param array $fieldConfig
     * @return mixed
     * @since 2.1.0
     */
    public function process($value, $scope, $scopeId, array $fieldConfig)
    {
        $backendModel = $this->backendModelFactory->createByPath(
            $fieldConfig['path'],
            [
                'value' => $value,
                'field_config' => $fieldConfig,
                'scope' => $scope,
                'scope_id' => $scopeId
            ]
        );
        $backendModel->afterLoad();
        return $backendModel->getValue();
    }
}
