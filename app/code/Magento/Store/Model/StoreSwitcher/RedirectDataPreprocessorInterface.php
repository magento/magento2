<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

/**
 * Store switcher redirect data pre-processor interface
 *
 * @api
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
