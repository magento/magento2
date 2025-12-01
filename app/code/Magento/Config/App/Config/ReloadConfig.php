<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace Magento\Config\App\Config;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\State\ReloadProcessorInterface;

/**
 * Config module specific reset state
 */
class ReloadConfig implements ReloadProcessorInterface
{
    /**
     * @param System $system
     */
    public function __construct(private readonly System $system)
    {
    }

    /**
     * Tells the system state to reload itself.
     *
     * @return void
     */
    public function reloadState(): void
    {
        $this->system->get();
    }
}
