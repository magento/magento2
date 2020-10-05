<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

/**
 * Store switcher redirect data post-processor interface
 */
interface RedirectDataPostprocessorInterface
{
    /**
     * Process data redirected from origin source
     *
     * @param ContextInterface $context
     * @param array $data
     */
    public function process(ContextInterface $context, array $data): void;
}
