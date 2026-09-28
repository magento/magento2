<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\ViewModel;

/**
 * View model interface for requirejs configuration modifier
 */
interface RequireJsConfigModifierInterface
{
    /**
     * Modifies requirejs configuration
     *
     * @param array $config requirejs configuration
     * @return array
     */
    public function modify(array $config): array;
}
