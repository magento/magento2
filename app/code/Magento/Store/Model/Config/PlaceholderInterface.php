<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Model\Config;

/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 */
interface PlaceholderInterface
{
    /**
     * Replace placeholders with config values
     *
     * @param array $data
     * @return array
     */
    public function process(array $data);
}
