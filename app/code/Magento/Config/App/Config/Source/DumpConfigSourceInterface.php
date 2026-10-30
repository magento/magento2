<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Config\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;

/**
 * Interface DumpConfigSourceInterface
 * @api
 * @since 100.1.2
 */
interface DumpConfigSourceInterface extends ConfigSourceInterface
{
    /**
     * Retrieves list of field paths were excluded from config dump
     *
     * @return array
     * @since 100.1.2
     */
    public function getExcludedFields();
}
