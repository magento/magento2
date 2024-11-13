<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Framework\App\State;

interface ReloadProcessorInterface
{
    /**
     * Tells the system state to reload itself.
     *
     * @return void
     */
    public function reloadState(): void;
}
