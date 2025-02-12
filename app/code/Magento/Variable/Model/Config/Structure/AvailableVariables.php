<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Variable\Model\Config\Structure;

/**
 * Provide configuration for available variables
 */
class AvailableVariables
{
    /**
     * @var string[]
     */
    private $configPaths;

    /**
     * @param string[] $configPaths
     */
    public function __construct(
        $configPaths = []
    ) {
        $this->configPaths = $configPaths;
    }

    /**
     * Provide configured System configuration paths
     *
     * @return string[]
     */
    public function getConfigPaths()
    {
        return $this->configPaths;
    }

    /**
     * Provide configured System configuration sections
     *
     * @return string[]
     */
    public function getFlatConfigPaths()
    {
        return array_merge(...array_values($this->configPaths));
    }
}
