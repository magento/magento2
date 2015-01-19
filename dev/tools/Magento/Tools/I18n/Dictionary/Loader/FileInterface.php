<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Dictionary\Loader;

/**
 * Dictionary loader interface
 */
interface FileInterface
{
    /**
     * Load dictionary
     *
     * @param string $file
     * @return \Magento\Tools\I18n\Dictionary
     * @throws \InvalidArgumentException
     */
    public function load($file);
}
