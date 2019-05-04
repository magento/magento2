<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Ui\Component\Column;

/**
 * Interface DataTypeConfigProviderInterface
 *
 * @package Magento\Catalog\Ui\Component\Column
 */
interface DataTypeConfigProviderInterface
{
    /**
     * Get config
     *
     * @return array
     */
    public function getConfig(): array;
}
