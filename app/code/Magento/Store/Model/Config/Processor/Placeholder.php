<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Store\Model\Config\Processor;

use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Store\Model\Config\Placeholder as ConfigPlaceholder;

/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 */
class Placeholder implements PostProcessorInterface
{
    /**
     * @var ConfigPlaceholder
     */
    private $configPlaceholder;

    /**
     * Placeholder constructor.
     * @param ConfigPlaceholder $configPlaceholder
     */
    public function __construct(ConfigPlaceholder $configPlaceholder)
    {
        $this->configPlaceholder = $configPlaceholder;
    }

    /**
     * @inheritdoc
     */
    public function process(array $data)
    {
        foreach ($data as $scope => &$scopeData) {
            if ($scope === 'default') {
                $scopeData = $this->configPlaceholder->process($scopeData);
            } else {
                foreach ($scopeData as &$sData) {
                    $sData = $this->configPlaceholder->process($sData);
                }
            }
        }

        return $data;
    }
}
