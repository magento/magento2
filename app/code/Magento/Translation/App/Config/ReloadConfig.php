<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Translation\App\Config;

use Magento\Framework\App\State\ReloadProcessorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Translation\App\Config\Type\Translation;

/**
 * Translation module specific reset state part
 */
class ReloadConfig implements ReloadProcessorInterface
{
    /**
     * Tells the system state to reload itself.
     *
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function reloadState(ObjectManagerInterface $objectManager)
    {
        $objectManager->get(Translation::class)->clean();
    }
}
