<?php
/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Processor;

use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Store\Model\Config\Placeholder as ConfigPlaceholder;

/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 * @package Magento\Store\Model\Config\Processor
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
