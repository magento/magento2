<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Customization;

/**
 * Theme customization configuration interface
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Get customization file types
     *
     * @return array Mappings of customization file types to its classes
     * @since 2.0.0
     */
    public function getFileTypes();
}
