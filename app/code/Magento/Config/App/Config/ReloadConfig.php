<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Config\App\Config;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\State\ReloadProcessorInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Config module specific reset state
 */
class ReloadConfig implements ReloadProcessorInterface
{
    public function __construct(private System $system)
    {}
    /**
     * Tells the system state to reload itself.
     *
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function reloadState()
    {
        $this->system->get();
    }
}
