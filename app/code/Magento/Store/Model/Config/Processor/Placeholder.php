<?php
/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Processor;

use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Store\Model\Config\Placeholder as ConfigPlaceholder;

/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 * @since 2.0.0
 */
class Placeholder implements PostProcessorInterface
{
    /**
     * @var ConfigPlaceholder
     * @since 2.2.0
     */
    private $configPlaceholder;

    /**
     * Placeholder constructor.
     * @param ConfigPlaceholder $configPlaceholder
     * @since 2.0.0
     */
    public function __construct(ConfigPlaceholder $configPlaceholder)
    {
        $this->configPlaceholder = $configPlaceholder;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
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
