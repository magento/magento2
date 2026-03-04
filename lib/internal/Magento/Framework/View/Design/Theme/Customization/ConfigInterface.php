<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Design\Theme\Customization;

/**
 * Theme customization configuration interface
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Get customization file types
     *
     * @return array Mappings of customization file types to its classes
     */
    public function getFileTypes();
}
