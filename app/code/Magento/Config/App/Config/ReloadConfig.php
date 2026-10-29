<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace Magento\Config\App\Config;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\State\ReloadProcessorInterface;

/**
 * Config module specific reset state
 */
class ReloadConfig implements ReloadProcessorInterface
{
    /**
     * @param ConfigTypeInterface $system
     */
    public function __construct(private readonly ConfigTypeInterface $system)
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
