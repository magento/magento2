<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;

/**
 * Interface DumpConfigSourceInterface
 * @api
 * @since 2.2.0
 */
interface DumpConfigSourceInterface extends ConfigSourceInterface
{
    /**
     * Retrieves list of field paths were excluded from config dump
     *
     * @return array
     * @since 2.2.0
     */
    public function getExcludedFields();
}
