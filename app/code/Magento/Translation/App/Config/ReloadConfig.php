<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Translation\App\Config;

use Magento\Framework\App\State\ReloadProcessorInterface;
use Magento\Translation\App\Config\Type\Translation;

/**
 * Translation module specific reset state part
 */
class ReloadConfig implements ReloadProcessorInterface
{
    /**
     * @param Translation $translation
     */
    public function __construct(private readonly Translation $translation)
    {
    }

    /**
     * Tells the system state to reload itself.
     *
     * @return void
     */
    public function reloadState(): void
    {
        $this->translation->clean();
    }
}
