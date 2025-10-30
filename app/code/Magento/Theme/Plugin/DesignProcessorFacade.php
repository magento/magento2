<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Plugin;

use Magento\Config\Console\Command\ConfigSet\ProcessorFacade;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Theme\Model\Data\Design\ConfigFactory;
use Magento\Theme\Model\Design\Config\Validator;

class DesignProcessorFacade
{
    /**
     * @param Validator $validator
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        private Validator $validator,
        private ConfigFactory $configFactory
    ) {
    }

    /**
     * Plugin to validate design configuration data before saving
     *
     * @param ProcessorFacade $subject
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string $scopeCode
     * @param bool $lock
     * @param string $lockTarget
     * @return string
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcessWithLockTarget(
        ProcessorFacade $subject,
        $path,
        $value,
        $scope,
        $scopeCode,
        $lock,
        $lockTarget = ConfigFilePool::APP_ENV
    ) {
        if (stripos($path, 'design/') === 0) {
            $savePath = str_replace('design/', '', $path);
            $savePath = str_replace('/', '_', $savePath);
            $designConfig = $this->configFactory->create($scope, $scopeCode, [$savePath => $value]);
            $this->validator->validate($designConfig);
        }

        return [$path, $value, $scope, $scopeCode, $lock, $lockTarget];
    }
}
