<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser;

/**
 * Adapter Interface
 */
interface AdapterInterface
{
    /**
     * Parse file
     *
     * @param string $file
     * @return array
     */
    public function parse($file);

    /**
     * Get parsed phrases
     *
     * @return array
     */
    public function getPhrases();
}
