<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Loader;

/**
 * Dictionary loader interface
 * @since 2.0.0
 */
interface FileInterface
{
    /**
     * Load dictionary
     *
     * @param string $file
     * @return \Magento\Setup\Module\I18n\Dictionary
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function load($file);
}
