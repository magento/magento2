<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

/**
 * Store switcher redirect data pre-processor interface
 */
interface RedirectDataPreprocessorInterface
{
    /**
     * Collect data to be redirected to target store
     *
     * @param ContextInterface $context
     * @param array $data
     * @return array
     */
    public function process(ContextInterface $context, array $data): array;
}
